<?php

namespace Pgs\RestfonyBundle\Generator;

use Pgs\RestfonyBundle\Manipulator\RestFormServiceManipulator;
use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class DoctrineFormGenerator extends Generator
{
    protected $bundlePath;
    protected $entity;
    protected $entityClass;
    protected $namespace;
    protected $metadata;

    /**
     * @param BundleInterface   $bundle         The bundle in which to create the class
     * @param string            $entity         The entity relative class name
     * @param ClassMetadataInfo $metadata       The entity metadata class
     */
    public function __construct(BundleInterface $bundle, $entity, ClassMetadataInfo $metadata)
    {
        if (count($metadata->getIdentifier()) > 1) {
            throw new \RuntimeException(__CLASS__ . ' does not support entity classes with multiple primary keys.');
        }

        $this->entityClass = $this->prepareEntityClass($entity);
        $this->namespace = $bundle->getNamespace();
        $this->bundlePath = $bundle->getPath();
        $this->entity = $entity;
        $this->metadata = $metadata;
    }

    protected function prepareEntityClass($entity)
    {
        return substr($entity, strpos($entity, '\\'));
    }

    /**
     * Generates the entity form class if it does not exist.
     *
     * @param bool $forceOverwrite
     */
    public function generate($forceOverwrite = false)
    {
        $formPath = $this->prepareFilePath('/Form/Type/', 'Type');

        if (!$this->canWriteToFile($formPath, $forceOverwrite)) {
            throw new \RuntimeException(sprintf(
                'Unable to generate the %s form class as it already exists under the file: %s',
                $this->entityClass . 'Type',
                $formPath
            ));
        }

        $this->renderFile(
            'form/FormType.php.twig',
            $formPath,
            [
                'fields'                => $this->getFieldsFromMetadata($this->metadata),
                'associations'          => $this->metadata->associationMappings,
                'namespace'             => $this->namespace,
                'entity_namespace'      => $this->prepareEntityNamespace($this->entity),
                'entity_class'          => $this->entityClass,
                'rest_support'          => true,
                'rest_form_type_name'   => $this->prepareFormAlias($this->entityClass),
                'form_type_name'        => $this->prepareFormTypeName($this->entity, $this->namespace, $this->entityClass)
            ]
        );

        $this->renderFile(
            'form/FormFilterType.php.twig',
            $this->prepareFilePath('/Form/Filter/', 'FilterType'),
            [
                'namespace'             => $this->namespace,
                'entity_namespace'      => $this->prepareEntityNamespace($this->entity),
                'entity_class'          => $this->entityClass,
                'rest_support'          => true,
                'rest_form_type_name'   => $this->prepareFormAlias($this->entityClass),
                'form_type_name'        => $this->prepareFormTypeName($this->entity, $this->namespace, $this->entityClass) . 'filter'
            ]
        );
    }

    protected function canWriteToFile($path, $forceOverwrite = false)
    {
        return !file_exists($path) || $forceOverwrite;
    }

    protected function prepareEntityNamespace($entity)
    {
        $result = [];
        preg_match(addslashes('/:(([A-z0-9\]+)\)?[A-z0-9]+$/'), $entity, $result);
        return isset($result[2]) ? $result[2] : '';
    }

    protected function prepareFormAlias($entityClass)
    {
        return ltrim(preg_replace_callback('|[A-Z]+|', function ($data) {
            return '_'.strtolower($data[0]);
        }, $entityClass), '_');
    }

    protected function prepareFormTypeName($entity, $namespace, $entityClass)
    {
        $parts = explode('\\', $entity);
        return strtolower(
            str_replace('\\', '_', $namespace)
            . ($parts ? '_' : '')
            . implode('_', $parts)
            . '_' . $entityClass
        );
    }

    protected function prepareFilePath($path, $postfix)
    {
        return $this->bundlePath . $path . str_replace('\\', '/', $this->entity) . $postfix . '.php';
    }

    /**
     * Returns an array of fields. Fields can be both column fields and
     * association fields.
     *
     * @param ClassMetadataInfo $metadata
     *
     * @return array $fields
     */
    protected function getFieldsFromMetadata(ClassMetadataInfo $metadata)
    {
        $fields = array_merge($metadata->fieldMappings, $metadata->getAssociationMappings());

        // Remove the primary key field if it's not managed manually
        if (!$metadata->isIdentifierNatural()) {
            foreach ($metadata->getIdentifier() as $identifier) {
                unset($fields[$identifier]);
            }
        }

        foreach ($metadata->getAssociationMappings() as $fieldName => $relation) {
            $multiTypes = array(
                ClassMetadataInfo::ONE_TO_MANY,
                ClassMetadataInfo::MANY_TO_MANY,
            );
            if (in_array($relation['type'], $multiTypes)) {
                $fields[$fieldName]['relatedType'] = 'collection';
            } else {
                $fields[$fieldName]['relatedType'] = 'entity';
            }

            $fields[$fieldName]['relatedEntityShortcut'] =
                $this->getEntityBundleShortcut($fields[$fieldName]['targetEntity']);
        }

        return $fields;
    }

    /**
     * Take an entity name and return the shortcut name
     * eg Acme\DemoBundle\Entity\Notes -> AcemDemoBundle:Notes.
     *
     * @param string $entity fully qualified class name of the entity
     */
    protected function getEntityBundleShortcut($entity)
    {
        // wrap in EntityManager's Class Metadata function avoid problems with cached proxy classes
        $path = explode('\Entity\\', $entity);

        return str_replace('\\', '', $path[0]).':'.$path[1];
    }
}
