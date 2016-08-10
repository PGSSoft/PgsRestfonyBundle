<?php

namespace Pgs\RestfonyBundle\Tests\Generator;

use PHPUnit\Framework\TestCase;
use Pgs\RestfonyBundle\Generator\DoctrineFormGenerator;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DoctrineFormGeneratorTest extends TestCase
{
    const BUNDLE = 'DummyBundle';
    const ENTITY = self::BUNDLE . '\DummyEntity';
    const TMP_DIR = '.tmp';

    /**
     * @var \PHPUnit_Framework_MockObject_MockBuilder
     */
    protected $generatorMockBuilder;

    public function setUp()
    {
        $this->generatorMockBuilder = $this->getMockBuilder(DoctrineFormGenerator::class)
            ->setConstructorArgs([$this->getBundleMock(), self::ENTITY, $this->getClassMetadataMock()]);
    }

    public function testConstructorNotSupportMultipleKeys()
    {
        $this->expectException(\RuntimeException::class);

        new DoctrineFormGenerator($this->getBundleMock(), self::ENTITY, $this->getMultiKeyClassMetadataMock());
    }

    public function testGenerate()
    {
        $generator = $this->generatorMockBuilder
            ->setMethods(['renderFile'])
            ->getMock();

        $generator
            ->expects($this->exactly(2))
            ->method('renderFile');

        $generator->generate();
    }

    public function testGenerateWillNotOverwrite()
    {
        $generator = $this->generatorMockBuilder
            ->setMethods(['canWriteToFile'])
            ->getMock();

        $generator->method('canWriteToFile')->willReturn(false);

        $this->expectException(\RuntimeException::class);

        $generator->generate();
    }

    protected function getBundleMock()
    {
        $bundleMock = $this->getMockBuilder(Bundle::class)
            ->setMethods(['getPath', 'getNamespace'])
            ->getMock();

        $bundleMock->method('getNamespace')->willReturn(self::BUNDLE);
        $bundleMock->method('getPath')->willReturn(self::TMP_DIR);

        return $bundleMock;
    }

    protected function getClassMetadataMock()
    {
        $mock = $this->getMockBuilder(ClassMetadataInfo::class)
            ->setConstructorArgs([static::ENTITY])
            ->setMethods(['getAssociationMappings', 'isIdentifierNatural', 'getIdentifier'])
            ->getMock();

        $mock->method('getAssociationMappings')
            ->willReturn([
                'field_1' => [
                    'type' => ClassMetadataInfo::ONE_TO_MANY,
                    'targetEntity' => 'Pgs\DummyBundle\Entity\DummyEntity1'
                ],
                'field_2' => [
                    'type' => ClassMetadataInfo::MANY_TO_ONE,
                    'targetEntity' => 'Pgs\DummyBundle\Entity\DummyEntity2'
                ]
            ]);
        $mock->method('isIdentifierNatural')->willReturn(false);
        $mock->method('getIdentifier')->willReturn(['field_3']);

        $mock->fieldMappings = ['field_3'];

        return $mock;
    }

    protected function getMultiKeyClassMetadataMock()
    {
        $mock = $this->getMockBuilder(ClassMetadataInfo::class)
            ->setConstructorArgs([static::ENTITY])
            ->setMethods(['getIdentifier'])
            ->getMock();

        $mock->method('getIdentifier')
            ->willReturn([1, 2]);

        return $mock;
    }
}
