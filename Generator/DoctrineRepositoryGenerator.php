<?php

namespace Pgs\RestfonyBundle\Generator;

use Pgs\RestfonyBundle\Generator\Helper\BundleStructureHelper;
use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class DoctrineRepositoryGenerator extends Generator
{
    /**
     * @var BundleStructureHelper
     */
    protected $helper;

    public function __construct(BundleInterface $bundle, $entity)
    {
        $this->helper = new BundleStructureHelper($bundle, $entity);
    }

    protected function canWriteToFile($forceOverwrite)
    {
        return !file_exists($this->helper->getRepositoryFullFilename()) || $forceOverwrite;
    }

    /**
     * Generates the entity form class if it does not exist.
     *
     * @param bool              $forceOverwrite
     */
    public function generate($forceOverwrite = false)
    {
        if (!$this->canWriteToFile($forceOverwrite)) {
            throw new \RuntimeException(sprintf(
                'Unable to generate the %s Repository class as it already exists under the file: %s',
                $this->helper->getEntityClass(),
                $this->helper->getRepositoryFullFilename()
            ));
        }

        $this->renderFile(
            'repository/repository.php.twig',
            $this->helper->getRepositoryFullFilename(),
            [
                'alias' => strtolower($this->helper->getEntityClass()[0]),
                'class' => $this->helper->getRepositoryClass(),
                'namespace' => $this->helper->getRepositoryNamespace()
            ]
        );
    }
}
