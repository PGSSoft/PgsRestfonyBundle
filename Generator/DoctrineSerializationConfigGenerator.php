<?php

namespace Pgs\RestfonyBundle\Generator;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation\ExclusionPolicy;
use Pgs\RestfonyBundle\Generator\Helper\BundleStructureHelper;
use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\Yaml\Yaml;

class DoctrineSerializationConfigGenerator extends Generator
{
    protected $groupPrefixes = [''];
    protected $groupSuffixes = ['_list', '_get'];

    protected $excludedRelationPrefixSuffixPairs = [
        ['', '_get'],
    ];

    /**
     * @var BundleStructureHelper
     */
    protected $helper;

    /**
     * DoctrineSerializationConfigGenerator constructor.
     * @param BundleInterface   $bundle   The bundle in which to create the class
     * @param string            $entity   The entity relative class name
     */
    public function __construct(BundleInterface $bundle, $entity)
    {
        $this->helper = new BundleStructureHelper($bundle, $entity);
    }

    /**
     * Generates the entity form class if it does not exist.
     *
     * @param ClassMetadataInfo $metadata The entity metadata class
     */
    public function generate(ClassMetadataInfo $metadata, $forceOverwrite = false)
    {
        $file = $this->helper->getResourcesDirname() . '/config/serializer/Entity.' . $this->helper->getEntityClass() . '.yml';

        if (!file_exists(dirname($file)) && !@mkdir(dirname($file), 0777, true)) {
            throw new IOException('Unable to create config/serializer directory');
        }

        if (!$this->canWriteToFile($file, $forceOverwrite)) {
            throw new \RuntimeException(sprintf(
                'Unable to generate the %s serialization config as it already exists under the file: %s',
                $this->helper->getEntityClass(),
                $file
            ));
        }

        $entityNameLower = $this->helper->camelToUnderscore($this->helper->getEntityClass());

        $data[$this->helper->getEntityFullClass()] = [
            'exclusion_policy' => ExclusionPolicy::ALL,
            'xml_root_name' => $this->helper->getEntityClass(),
            'properties' => $this->getPropertiesFromMetadata($entityNameLower, $metadata),
            'relations' => $this->getRelationsFromMetadata($entityNameLower, $metadata),
        ];

        $this->writeYamlToFile($file, $data);

        foreach ($metadata->getAssociationMappings() as $association) {
            if ($association['targetEntity']) {
                $this->generateForEntity($association['targetEntity'], dirname($file), $entityNameLower);
            }
        }
    }

    protected function canWriteToFile($file, $forceOverwrite)
    {
        return !file_exists($file) || $forceOverwrite;
    }

    protected function writeYamlToFile($filePath, array $data)
    {
        $yaml = Yaml::dump($data, 4);
        file_put_contents($filePath, $yaml);
    }

    /**
     * @param string $targetClass
     * @param string $dirPath
     * @param string $entityNameLower
     */
    private function generateForEntity($targetClass, $dirPath, $entityNameLower)
    {
        $targetClassName = basename($targetClass);

        $targetPath = $dirPath . '/Entity.' . $targetClassName . '.yml';
        $targetData = $this->getYamlFileContent($targetPath);

        if (isset($targetData[$targetClass])) {
            foreach ($targetData[$targetClass]['properties'] as &$property) {
                $property['groups'] = array_merge(
                    $property['groups'],
                    $this->getGroupsFromMetadata($entityNameLower)
                );
            }
        }

        if (!empty($targetData)) {
            $this->writeYamlToFile($targetPath, $targetData);
        }
    }

    protected function getYamlFileContent($filePath)
    {
        return file_exists($filePath) ? Yaml::parse($filePath) : [];
    }

    /**
     * Returns an array of fields. Fields can be both column fields and
     * association fields.
     *
     * @param $entityName
     * @param ClassMetadataInfo $metadata
     *
     * @return array $fields
     */
    protected function getPropertiesFromMetadata($entityName, ClassMetadataInfo $metadata)
    {
        $fields = array_merge($metadata->fieldMappings, $metadata->getAssociationMappings());

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
        }

        $result = [];

        foreach ($fields as $fieldName => $field) {
            $item = [
                'expose' => true,
                'groups' => $this->getGroupsFromMetadata($entityName),
            ];
            if (!empty($field['id'])) {
                $item['xml_attribute'] = true;
            }
            if ($field['type'] === 'date') {
                $item['type'] = "DateTime<'Y-m-d'>";
            }
            $result[$fieldName] = $item;
        }

        return $result;
    }

    protected function getGroupsFromMetadata($entityName)
    {
        $result = [];

        foreach ($this->groupPrefixes as $prefix) {
            foreach ($this->groupSuffixes as $suffix) {
                $result[] = $prefix.$entityName.$suffix;
            }
        }

        return $result;
    }

    private function getRelationsFromMetadata($entityName)
    {
        $result = [];

        $groups = [];
        foreach ($this->excludedRelationPrefixSuffixPairs as $pair) {
            $groups[] = $pair[0] . $entityName . $pair[1];
        }

        $result[] = [
            'rel' => 'self',
            'href' => "expr('/api/v1/products/' ~ object.getId())",
            'exclusion' => [
                'groups' => $groups,
            ],
        ];

        return $result;
    }
}
