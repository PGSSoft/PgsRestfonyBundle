<?php

namespace Pgs\RestfonyBundle\Tests\Manager;

use Doctrine\ORM\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Hateoas\Configuration\Metadata\ClassMetadata;
use Pgs\RestfonyBundle\Manager\AbstractManager;
use Pgs\RestfonyBundle\Repository\RestRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

/**
 * @covers Pgs\RestfonyBundle\Manager\AbstractManager
 */
class AbstractManagerTest extends TestCase
{
    /** @var AbstractManager */
    private $restCollectionType;

    protected function setUp()
    {
        $repository = $this->prophesize(RestRepository::class);
        $repository->find(Argument::type('int'))->willReturn('objectForFind');
        $repository->findAll()->willReturn('collectionForFindAll');
        $repository->findBy(
            Argument::type('array'),
            Argument::type('array'),
            Argument::type('int'),
            Argument::type('int')
        )->willReturn('collectionForFindABy');
        $repository->findOneBy(Argument::type('array'))->willReturn('objectForFindOneBy');
        $repository->getClassName()->willReturn('theNameOfTheClass');
        $repository->getListQuery(Argument::type('array'), Argument::type('string'))->willReturn(true);

        $classMetadata = $this->prophesize(ClassMetadata::class);
        $classMetadata->getName()->willReturn(self::class);

        $objectManager = $this->prophesize(EntityManagerInterface::class);
        $objectManager->getRepository('Dummy')->willReturn($repository->reveal());
        $objectManager->getClassMetadata('Dummy')->willReturn($classMetadata->reveal());
        $objectManager->merge(Argument::type('object'))->willReturn(true);
        $objectManager->persist(Argument::type('object'))->willReturn(true);
        $objectManager->remove(Argument::type('object'))->willReturn(true);
        $objectManager->flush()->willReturn(true);
        $objectManager->createQueryBuilder()->willReturn(new class
            {
            public function __call($name, $arguments)
            {
                return method_exists($this, $name) ? $this->$name(...$arguments) : $this;
            }

            public function getMyName()
            {
                return 'classPretendingToBeQueryBuilder';
            }
        });

        $this->restCollectionType = new AbstractManager($objectManager->reveal(), 'Dummy');
    }

    /**
     * @test
     */
    public function find()
    {
        $this->assertSame('objectForFind', $this->restCollectionType->find(1));
    }

    /**
     * @test
     */
    public function findAll()
    {
        $this->assertSame('collectionForFindAll', $this->restCollectionType->findAll());
    }

    /**
     * @test
     */
    public function findBy()
    {
        $this->assertSame('collectionForFindABy', $this->restCollectionType->findBy([], [], 10, 20));
    }

    /**
     * @test
     */
    public function findOneBy()
    {
        $this->assertSame('objectForFindOneBy', $this->restCollectionType->findOneBy([]));
    }

    /**
     * @test
     */
    public function getClassName()
    {
        $this->assertSame('theNameOfTheClass', $this->restCollectionType->getClassName());
    }

    /**
     * @test
     */
    public function merge()
    {
        $this->assertTrue($this->restCollectionType->merge($this->restCollectionType->create(), true));
    }

    /**
     * @test
     */
    public function persist()
    {
        $this->assertNull($this->restCollectionType->persist($this->restCollectionType->create(), true));
    }

    /**
     * @test
     */
    public function remove()
    {
        $this->assertNull($this->restCollectionType->remove($this->restCollectionType->create(), true));
    }

    /**
     * @test
     */
    public function createList()
    {
        $this->assertSame(
            'classPretendingToBeQueryBuilder',
            $this->restCollectionType->createList('alias', 'idAlias')->getMyName()
        );
    }

    /**
     * @test
     */
    public function getListQuery()
    {
        $this->assertTrue($this->restCollectionType->getListQuery([], 'sortQuery'));
    }
}
