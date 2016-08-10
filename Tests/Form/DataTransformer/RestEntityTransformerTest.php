<?php

namespace Pgs\RestfonyBundle\Tests\Form\DataTransformer;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Pgs\RestfonyBundle\Form\DataTransformer\RestEntityTransformer;
use Pgs\RestfonyBundle\Tests\Controller\RestProphecyTestCase;
use Pgs\RestfonyBundle\Tests\Form\Dummy;
use PHPUnit_Framework_MockObject_MockObject;
use RuntimeException;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @covers Pgs\RestfonyBundle\Form\DataTransformer\RestEntityTransformer
 */
class RestEntityTransformerTest extends RestProphecyTestCase
{
    /** @var PHPUnit_Framework_MockObject_MockObject */
    private $classMetadata;

    /** @var RestEntityTransformer */
    private $restEntityTransformer;

    protected function setUp()
    {
        $this->classMetadata = $this->createMock(ClassMetadata::class);
        $this->classMetadata->method('setIdentifier')->willReturn('Dummy');
        $this->classMetadata->method('getIdentifierValues')->willReturn(['Dummy' => 'Dummy']);

        $repository = $this->createMock(ObjectRepository::class);
        $repository->method('find')->willReturnCallback(function ($id) {
                return $id === 1 ? new Dummy() : null;
        });

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager
            ->method('getClassMetadata')
            ->with(Dummy::class)
            ->willReturn($this->classMetadata);
        $objectManager
            ->method('getRepository')
            ->willReturn($repository);

            $this->restEntityTransformer = new RestEntityTransformer($objectManager, 'Dummy');
    }

    /**
     * @test
     */
    public function returnNullIfEntityPassedNull()
    {
        $this->assertNull($this->restEntityTransformer->transform(null));
    }

    /**
     * @test
     */
    public function returnItselfIfPassedString()
    {
        $this->assertSame('Dummy', $this->restEntityTransformer->transform('Dummy'));
    }

    /**
     * @test
     */
    public function returnToStringValueForObjectConvertibleToSting()
    {
        $this->assertSame('Dummy', $this->restEntityTransformer->transform('Dummy'));
    }

    /**
     * @test
     */
    public function returnClassNameForObject()
    {
        $this->classMetadata->method('getIdentifierFieldNames')->willReturn(['Dummy']);

        $object = new Dummy();

        $this->assertSame('Dummy', $this->restEntityTransformer->transform($object));
    }

    /**
     * @test
     */
    public function throwExceptionForMultipleIdentifiers()
    {
        $this->classMetadata->method('getIdentifierFieldNames')->willReturn(['Dummy', 'Dummy2']);

        $object = new Dummy();

        $this->expectException(RuntimeException::class);

        $this->assertSame('Dummy', $this->restEntityTransformer->transform($object));
    }

    /**
     * @test
     */
    public function returnNullForReverseTransformForFalse()
    {
        $this->assertNull($this->restEntityTransformer->reverseTransform(null));
    }

    /**
     * @test
     */
    public function returnEntityWhenExists()
    {
        $this->assertInstanceOf(Dummy::class, $this->restEntityTransformer->reverseTransform(1));
    }

    /**
     * @test
     */
    public function throwExceptionForNotExistingEntity()
    {
        $this->expectException(TransformationFailedException::class);

        $this->restEntityTransformer->reverseTransform(404);
    }
}
