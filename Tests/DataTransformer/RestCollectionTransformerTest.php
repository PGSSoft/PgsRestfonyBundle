<?php

namespace Pgs\RestfonyBundle\Tests\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManager;
use Pgs\RestfonyBundle\Form\DataTransformer\RestCollectionTransformer;
use Pgs\RestfonyBundle\Tests\Controller\RestProphecyTestCase;
use Pgs\RestfonyBundle\Tests\DataTransformer\Fixtures\Product;
use Prophecy\Argument;
use RuntimeException;
use stdClass;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @author Karol Joński <kjonski@pgs-soft.com>
 */
class RestCollectionTransformerTest extends RestProphecyTestCase
{
    /**
     * @var RestCollectionTransformer
     */
    private $transformer;
    private $em;
    private $emStub;
    private $metadataStub;

    public function setup()
    {
        $this->em = $this->getEntityManagerMock();
        $this->transformer = new RestCollectionTransformer($this->em, Product::class);
        $this->metadataStub = $this->prophesize(ClassMetadata::class);
    }

    /**
     * @test
     */
    public function itShouldThrowExceptionIfArgumentIsNotCollectionWhenTransformMethodIsInvoked()
    {
        $this->expectException(TransformationFailedException::class);

        $this->transformer->transform(new stdClass());
    }

    /**
     * @test
     * @dataProvider nullAndEmptyCollectionDataProvider
     */
    public function itShouldReturnEmptyArrayIfCollectionIsNullOrEmptyWhenTransformMethodIsInvoked($collection)
    {
        $resultArray = $this->transformer->transform($collection);
        $this->assertInternalType('array', $resultArray);
        $this->assertEmpty($resultArray);
    }

    /**
     * @dataProvider entitiesIdsDataProvider
     * @param array $entitiesIds
     * @test
     */
    public function returnArrayOfEntitiesIdsIfCollectionContainsEntitiesWhenTransformMethodIsInvoked(array $entitiesIds)
    {
        $collection = $this->getEntityCollectionMock($entitiesIds);
        $result = $this->transformer->transform($collection);

        $this->assertSame($entitiesIds, $result);
    }

    /**
     * @test
     */
    public function itShouldThrowExceptionIfEntityHasMultipleIdentifiersWhenTransformMethodIsInvoked()
    {
        $collection = new ArrayCollection([
            $this->getMultipleIdentifierEntityMock()
        ]);

        $this->expectException(RuntimeException::class);

        $this->transformer->transform($collection);
    }

    /**
     * @test
     */
    public function itShouldThrowExceptionIfObjectIsNotArrayCollectionWhenReverseTransformIsInvoked()
    {
        $this->expectException(TransformationFailedException::class);

        $this->transformer->reverseTransform(new stdClass());
    }

    /**
     * @test
     * @dataProvider emptyArrayAndEmptyCollectionDataProvider
     * @param Collection $collection
     * @param Collection $expected
     */
    public function returnEmptyCollectionIfCollectionIsEmptyWhenReverseTransformIsInvoked($collection, $expected)
    {
        $result = $this->transformer->reverseTransform($collection);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     * @dataProvider entitiesIdsDataProvider
     * @param array $entitiesIds
     */
    public function returnCollectionOfEntitiesIfCollectionIsNotEmptyWhenReverseTransformIsInvoked(array $entitiesIds)
    {
        $result = $this->transformer->reverseTransform($entitiesIds);
        $expected = $result->map(function ($el) {
            return $el->getId();
        })->toArray();
        $this->assertEquals($expected, $entitiesIds);
    }

    /**
     * @dataProvider notFoundEntitiesIdsDataProvider
     * @param array $collection
     * @test
     */
    public function itShouldThrowExceptionIfEntityDoesNotExistWhenReverseTransformIsInvoked(array $collection)
    {
        $this->expectException(TransformationFailedException::class);

        $this->transformer->reverseTransform($collection);
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManagerMock()
    {
        $this->emStub = $this->prophesize(EntityManager::class);
        $th = $this;
        $this->emStub->getRepository(Argument::type('string'))->will(function ($args) use ($th) {
            return $th->getEntityRepositoryMock($args[0]);
        });
        $this->em = $this->emStub->reveal();

        return $this->em;
    }

    /**
     * @param array $entitiesIds
     * @return ArrayCollection
     */
    private function getEntityCollectionMock(array $entitiesIds = array())
    {
        $collection = new ArrayCollection();
        $testEntityClass = Product::class;
        foreach ($entitiesIds as $id) {
            $collection->add($this->getEntityMock($testEntityClass, $id));
        }
        return $collection;
    }

    /**
     * @param string $entityClass
     * @param int $id
     * @param array $identifierFieldNames
     * @return object
     */
    private function getEntityMock($entityClass, $id, $identifierFieldNames = ['id'])
    {
        $product = $this
            ->prophesize($entityClass);
        $product->getId()->willReturn($id);
        $entityMock = $product->reveal();

        $metadataMock = $this->getEntityMetadataMock($entityMock, $id, $identifierFieldNames);
        $this->emStub->getClassMetadata(get_class($entityMock))->willReturn($metadataMock);
        $this->em = $this->emStub->reveal();

        return $entityMock;
    }

    /**
     * @param string $entityClass
     * @return object
     */
    protected function getEntityRepositoryMock($entityClass)
    {
        $entityRepositoryClass = sprintf('%sRepository', $entityClass);
        $entityRepository = $this->prophesize($entityRepositoryClass);
        $th = $this;
        $entityRepository->find(Argument::any())->will(function ($args) use ($entityClass, $th) {
            return $args[0] === 'notFound' ? null : $th->getEntityMock($entityClass, $args[0]);
        });
        return $entityRepository->reveal();
    }

    /**
     * @param string $entityName
     * @param int $id
     * @param array $identifierFieldNames
     * @return ClassMetadata
     */
    protected function getEntityMetadataMock($entityName, $id = 1, array $identifierFieldNames = ['id'])
    {
        $this->metadataStub->getIdentifierFieldNames()->willReturn($identifierFieldNames);
        $class = Argument::type(get_class($entityName));
        $this->metadataStub->getIdentifierValues($class)->willReturn(['id' => $id]);

        return $this->metadataStub->reveal();
    }

    private function getMultipleIdentifierEntityMock()
    {
        return $this->getEntityMock(Product::class, 1, ['id', 'name']);
    }

    public function entitiesIdsDataProvider()
    {
        return [
            [
                [1, 17, 123],
            ],
            [
                [56, 'test'],
            ],
            [
                [1, 1, 2],
            ]
        ];
    }

    public function notFoundEntitiesIdsDataProvider()
    {
        return [
            [
                ['notFound'],
                [ new stdClass()]
            ]
        ];
    }

    public function nullAndEmptyCollectionDataProvider()
    {
        return [
            [null],
            [new ArrayCollection()]
        ];
    }

    public function emptyArrayAndEmptyCollectionDataProvider()
    {
        return [
            [
                [],
                new ArrayCollection()
            ],
            [
                new ArrayCollection(),
                new ArrayCollection()
            ]
        ];
    }
}
