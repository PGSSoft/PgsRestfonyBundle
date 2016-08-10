<?php

namespace Pgs\RestfonyBundle\Generator;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use InvalidArgumentException;
use Pgs\RestfonyBundle\Generator\Helper\BundleStructureHelper;
use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use RuntimeException;

class DoctrineCrudGenerator extends Generator
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var BundleStructureHelper
     */
    protected $helper;

    /** @var BundleInterface */
    protected $bundle;

    protected $entity;
    protected $metadata;
    protected $actions;

    /**
     * Constructor.
     *
     * @param Filesystem $filesystem A Filesystem instance
     * @param BundleInterface $bundle
     * @param string $entity
     */
    public function __construct(Filesystem $filesystem, BundleInterface $bundle, $entity)
    {
        $this->filesystem = $filesystem;
        $this->helper = new BundleStructureHelper($bundle, $entity);
    }

    /**
     * Generate the CRUD controller.
     *
     * @param BundleInterface   $bundle           A bundle object
     * @param string            $entity           The entity relative class name
     * @param ClassMetadataInfo $metadata         The entity class metadata
     * @param array             $needWriteActions Whether or not to generate write actions
     * @param $forceOverwrite
     */
    public function generate(
        BundleInterface $bundle,
        $entity,
        ClassMetadataInfo $metadata,
        $needWriteActions,
        $forceOverwrite
    ) {
        $this->actions = $needWriteActions
            ? ['cget', 'get', 'post', 'put', 'patch', 'delete', 'new', 'edit']
            : ['cget', 'get'];

        if (count($metadata->getIdentifier()) !== 1) {
            throw new RuntimeException('CRUD generator does not support entities with multiple or no primary keys.');
        }

        $this->entity   = $entity;
        $this->bundle   = $bundle;
        $this->metadata = $metadata;

        $this->generateControllerClass($forceOverwrite);
        $this->generateTestClass();
    }

    /**
     * Generates the controller class only.
     *
     * @param bool $forceOverwrite
     */
    protected function generateControllerClass($forceOverwrite)
    {
        $metadata = $this->metadata;

        $target = $this->helper->getControllerFullFilename();

        $parts = explode('\\', $this->entity);
        array_pop($parts);
        $entityNamespace = implode('\\', $parts);

        if (!$this->canWriteToFile($target, $forceOverwrite)) {
            throw new \RuntimeException('Unable to generate the controller as it already exists.');
        }

        $idType = $this->getIdentifierType($metadata);

        $this->renderFile('controller/controller.php.twig', $target, array(
            'actions'           => $this->actions,
            'entity'            => $this->entity,
            'namespace'         => $this->bundle->getNamespace(),
            'entity_namespace'  => $entityNamespace,
            'entity_identifier_type' => $idType,
            'entity_identifier'      => $this->getEntityIdentifier($metadata),
            'requirement_regex'      => $this->getRequirementRegex($idType),
            'document' => true,
            'form_type' => $this->bundle->getNamespace()."\\Form\\Type\\".$this->entity."Type.php",
        ));
    }

    /**
     * Generates the functional test class only.
     */
    protected function generateTestClass()
    {
        $target = $this->helper->getTestsDirname() . '/Controller/' . $this->helper->getControllerClass() . 'Test.php';

        $this->renderFile('controller/controller-test.php.twig', $target, array(
            'entity'            => $this->entity,
            'namespace'         => $this->bundle->getNamespace(),
            'actions'           => $this->actions,
        ));
    }

    protected function canWriteToFile($file, $forceOverwrite)
    {
        return !file_exists($file) || $forceOverwrite;
    }

    /**
     * @param ClassMetadataInfo $metadata
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    private function getIdentifierType(ClassMetadataInfo $metadata)
    {
        $identifier = array_values($metadata->getIdentifier())[0];
        foreach ($metadata->fieldMappings as $field) {
            if ($field['fieldName'] === $identifier) {
                return $field['type'];
            }
        }

        return '';
    }

    /**
     * @param ClassMetadataInfo $metadata
     *
     * @return mixed
     */
    private function getEntityIdentifier(ClassMetadataInfo $metadata)
    {
        return array_values($metadata->getIdentifier())[0];
    }

    /**
     * @param string $idType
     *
     * @return string
     */
    private function getRequirementRegex($idType)
    {
        switch ($idType) {
            case 'string':
                return '\w+';
            case 'integer':
                return '\d+';
            default:
                return '';
        }
    }
}
