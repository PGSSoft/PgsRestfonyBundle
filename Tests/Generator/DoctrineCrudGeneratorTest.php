<?php

namespace Pgs\RestfonyBundle\Tests\Generator;

use PHPUnit\Framework\TestCase;
use Pgs\RestfonyBundle\Generator\DoctrineCrudGenerator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class DoctrineCrudGeneratorTest extends TestCase
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
        $this->generatorMockBuilder = $this->getMockBuilder(DoctrineCrudGenerator::class)
            ->setConstructorArgs([$this->createMock(Filesystem::class), $this->getBundleMock(), self::ENTITY]);
    }

    /**
     * @return array
     */
    public function generateProvider()
    {
        return [
            ['field_3', 'string'],
            ['field_3', 'integer'],
            ['field_4', 'string'],
        ];
    }

    /**
     * @dataProvider generateProvider
     * @param string $identifier
     * @param string $type
     */
    public function testGenerate($identifier, $type)
    {
        $generator = $this->generatorMockBuilder
            ->setMethods(['renderFile'])
            ->getMock();

        $generator
            ->expects($this->exactly(2))
            ->method('renderFile');

        $generator->generate($this->getBundleMock(), self::ENTITY, $this->getClassMetadataMock($identifier, $type), true, false);
    }

    public function testGenerateWillNotOverwrite()
    {
        $generator = $this->generatorMockBuilder
            ->setMethods(['canWriteToFile'])
            ->getMock();

        $generator->method('canWriteToFile')->willReturn(false);

        $this->expectException(\RuntimeException::class);

        $generator->generate($this->getBundleMock(), self::ENTITY, $this->getClassMetadataMock(), false, false);
    }

    public function testGenerateOnlySupportingOneKey()
    {
        $generator = $this->generatorMockBuilder
            ->setMethods(['canWriteToFile'])
            ->getMock();

        $generator->method('canWriteToFile')->willReturn(false);

        $this->expectException(\RuntimeException::class);

        $generator->generate($this->getBundleMock(), self::ENTITY, $this->getMultiKeyClassMetadataMock(), false, false);
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

    /**
     * @param string $identifier
     * @param string $type
     * @return ClassMetadataInfo
     */
    protected function getClassMetadataMock($identifier = 'field_3', $type = 'string')
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
                ],
                'field_3' => [
                    'id' => 'id',
                    'type' => 'date',
                    'targetEntity' => 'Pgs\DummyBundle\Entity\DummyEntity2'
                ]
            ]);
        $mock->method('isIdentifierNatural')->willReturn(false);
        $mock->method('getIdentifier')->willReturn(['field_3']);

        $mock->fieldMappings = [['fieldName' => $identifier, 'type' => $type]];

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
