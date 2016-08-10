<?php

namespace Pgs\RestfonyBundle\Generator;

use Pgs\RestfonyBundle\Generator\Helper\BundleStructureHelper;
use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class DoctrineManagerGenerator extends Generator
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
        return !file_exists($this->helper->getManagerFullFilename()) || $forceOverwrite;
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
                'Unable to generate the %s manager class as it already exists under the file: %s',
                $this->helper->getEntityClass() . 'Type',
                $this->helper->getManagerFullFilename()
            ));
        }

        $this->renderFile(
            'manager/manager.php.twig',
            $this->helper->getManagerFullFilename(),
            [
                'repository_class' => $this->helper->getRepositoryFullClass(),
                'class' => $this->helper->getManagerClass(),
                'namespace' => $this->helper->getManagerNamespace()
            ]
        );

        $this->renderFile(
            'manager/interface.php.twig',
            $this->helper->getManagerFullFilename($this->helper->getManagerClass() . 'Interface.php'),
            [
                'bundle_namespace' => $this->helper->getBundleNamespace(),
                'entity_class' => $this->helper->getEntityClass(),
                'class' => $this->helper->getManagerClass(),
                'namespace' => $this->helper->getManagerNamespace()
            ]
        );
    }
}
