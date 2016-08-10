<?php

namespace Pgs\RestfonyBundle\Manipulator;

use Doctrine\ORM\Mapping\ClassMetadata;
use Sensio\Bundle\GeneratorBundle\Manipulator\Manipulator;
use Symfony\Component\Yaml\Yaml;

/**
 * Changes the PHP code of a YAML routing file.
 *
 * @author Lech Groblewicz <lgroblewicz@pgs-soft.com>
 */
class RestConfigManipulator extends Manipulator
{
    private $file;

    /**
     * Constructor.
     *
     * @param string $file The YAML routing file path
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * Adds a routing resource at the top of the existing ones.
     *
     * @param string $bundle
     * @param string $entity
     * @param array  $fields
     *
     * @return bool true if it worked, false otherwise
     */
    public function addResource($bundle, $entity, $fields)
    {
        $code = strtolower($entity);

        if (!file_exists($this->file)) {
            if (!is_writable(dirname($this->file))) {
                throw new \RuntimeException('Rest config file doesn\'t exist');
            }
            touch($this->file);
        }

        $yaml = Yaml::parse(file_get_contents($this->file), Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE);

        if (!isset($yaml['pgs_restfony']['modules'])) {
            $yaml['pgs_restfony']['modules'] = [];
        }

        $yaml['pgs_restfony']['modules'][$code] = $this->getDefaultConfig($bundle, $entity, $fields);

        if (false === file_put_contents($this->file, Yaml::dump($yaml, 5))) {
            return false;
        }

        return true;
    }

    protected function getDefaultConfig($bundle, $entity, $fields)
    {
        $alias = $this->getAlias($entity);

        return [
            'entity' => sprintf('%s\Entity\%s', $bundle, $entity),
            'form' => sprintf('%s\Form\Type\%sType', $bundle, $entity),
            'filter' => sprintf('%s\Form\Filter\%sFilterType', $bundle, $entity),
            'controller' => sprintf('%s\Controller\%sController', $bundle, $entity),
            'manager' => sprintf('%s\Manager\%sManager', $bundle, $entity),
            'sorts' => $this->getSorts($fields[0], $alias),
        ];
    }

    protected function getSorts(ClassMetadata $fields, $alias)
    {
        $sorts = [];
        foreach ($fields->fieldNames as $field) {
            $sorts[$field] = $alias.'.'.$field;
        }

        return $sorts;
    }

    protected function getAlias($name)
    {
        if (preg_match_all('#([A-Z]+)#', $name, $matches)) {
            return strtolower(implode('', $matches[1]));
        } else {
            return '';
        }
    }
}
