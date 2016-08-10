<?php

namespace Pgs\RestfonyBundle\Form\DataTransformer;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Transforms Relationship data for entity based forms.
 */
class RestCollectionTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    private $entityManager;

    /**
     * The name of the entity we are working with.
     *
     * @var string
     */
    private $entityName;

    /**
     * @param ObjectManager $entityManager
     * @param string $entityName
     */
    public function __construct(ObjectManager $entityManager, $entityName)
    {
        $this->entityManager = $entityManager;
        $this->entityName = $entityName;
    }

    /**
     * Transforms an entity collection to an array of identifiers.
     *
     * @param Collection|null $collection
     *
     * @return string[]
     */
    public function transform($collection)
    {
        if (empty($collection)) {
            return [];
        }

        if (!$collection instanceof Collection) {
            throw new TransformationFailedException(sprintf(
                '%s is not an instance of %s',
                gettype($collection),
                'Doctrine\Common\Collections\Collection'
            ));
        }

        return $collection->map(function ($entity) {
            try {
                $entityString = (string) $entity;
            } catch (\Exception $e) {
                $metadata = $this->entityManager->getClassMetadata(get_class($entity));
                $entityString = $this->getEntityIdentifier($metadata, $entity);
            }

            return $entityString;
        })->toArray();
    }

    /**
     * Transforms an array of ids to an array of entities.
     *
     * @param Collection|array $collection
     *
     * @return Collection
     *
     * @throws TransformationFailedException if entity is not found.
     */
    public function reverseTransform($collection)
    {
        //convert plain arrays to a doctrine collection
        if (is_array($collection)) {
            $collection = new ArrayCollection($collection);
        }

        if (!$collection instanceof Collection) {
            throw new TransformationFailedException(sprintf(
                '%s is not an instance of %s',
                gettype($collection),
                'Doctrine\Common\Collections\Collection'
            ));
        }

        if ($collection->isEmpty()) {
            return $collection;
        }

        return $collection->map(function ($id) {
            $entity = $this->entityManager
                ->getRepository($this->entityName)
                ->find($id)
            ;

            if (null === $entity) {
                throw new TransformationFailedException(sprintf(
                    'A %s with id "%s" does not exist!',
                    $this->entityName,
                    $id
                ));
            }

            return $entity;
        });
    }

    /**
     * @param ClassMetadata $metadata
     * @param mixed         $entity   The entity.
     *
     * @throws \RuntimeException
     *
     * @return mixed
     */
    protected function getEntityIdentifier(ClassMetadata $metadata, $entity)
    {
        if (count($metadata->getIdentifierFieldNames()) !== 1) {
            throw new \RuntimeException('Only one identifier allowed at this time.');
        }

        return $metadata->getIdentifierValues($entity)[$metadata->getIdentifierFieldNames()[0]];
    }
}
