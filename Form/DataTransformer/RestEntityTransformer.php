<?php

namespace Pgs\RestfonyBundle\Form\DataTransformer;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Exception;
use RuntimeException;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Transforms Relationship data for entity based forms.
 */
class RestEntityTransformer implements DataTransformerInterface
{
    /** @var ObjectManager */
    private $objectManager;

    /** @var string */
    private $entityName;

    /**
     * @param ObjectManager $objectManager
     * @param string $entityName
     */
    public function __construct(ObjectManager $objectManager, $entityName)
    {
        $this->objectManager = $objectManager;
        $this->entityName = $entityName;
    }

    /**
     * Transforms an entity to a string: __toString or id, in that order.
     *
     * @param string|null $entity
     *
     * @return string|null
     */
    public function transform($entity)
    {
        if (null === $entity) {
            return null;
        }

        try {
            $entityString = (string) $entity;
        } catch (Exception $e) {
            $metadata = $this->objectManager->getClassMetadata(get_class($entity));
            $entityString = $this->getEntityIdentifier($metadata, $entity);
        }

        return $entityString;
    }

    /**
     * Transforms an id to an entity.
     *
     * @param string $identifier
     *
     * @throws TransformationFailedException if entity is not found.
     *
     * @return null|object
     */
    public function reverseTransform($identifier)
    {
        if (!$identifier) {
            return null;
        }

        $entity = $this->objectManager
            ->getRepository($this->entityName)
            ->find($identifier);

        if (null === $entity) {
            throw new TransformationFailedException(sprintf(
                'A %s with id "%s" does not exist!',
                $this->entityName,
                $identifier
            ));
        }

        return $entity;
    }

    /**
     * @param ClassMetadata $metadata
     * @param mixed $entity The entity.
     *
     * @throws RuntimeException if multiple ids.
     *
     * @return mixed
     */
    protected function getEntityIdentifier(ClassMetadata $metadata, $entity)
    {
        if (count($metadata->getIdentifierFieldNames()) !== 1) {
            throw new RuntimeException('Only one identifier allowed at this time.');
        }

        return $metadata->getIdentifierValues($entity)[$metadata->getIdentifierFieldNames()[0]];
    }
}
