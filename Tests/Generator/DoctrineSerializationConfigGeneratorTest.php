<?php

namespace Pgs\RestfonyBundle\Tests\Generator;

use PHPUnit\Framework\TestCase;
use Pgs\RestfonyBundle\Generator\DoctrineSerializationConfigGenerator;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class DoctrineSerializationConfigGeneratorTest extends TestCase
{
    const BUNDLE = 'DummyBundle';
    const ENTITY = self::BUNDLE . '\DummyEntity';
    const TMP_DIR = '.tmp';

    protected function tearDown()
    {
        system('rm -rf ' . __DIR__ . '/../../.tmp');
    }

    public function testGenerate()
    {
        $generator = new DoctrineSerializationConfigGenerator($this->getBundleMock(), self::ENTITY);

        $this->assertNull($generator->generate($this->getClassMetadataMock()));
    }

    public function testGenerateWithMock()
    {
        $generatorMockBuilder = $this->getMockBuilder(DoctrineSerializationConfigGenerator::class)
            ->setConstructorArgs([$this->getBundleMock(), self::ENTITY]);

        $generator = $generatorMockBuilder
            ->setMethods(['writeYamlToFile', 'getYamlFileContent'])
            ->getMock();

        $generator
            ->expects($this->atLeastOnce())
            ->method('writeYamlToFile');

        $generator
            ->method('getYamlFileContent')
            ->willReturn([
                'Pgs\DummyBundle\Entity\DummyEntity1' => [
                    'properties' => [ 'property1' => ['groups' => []] ]
                ]
            ]);

        $generator->generate($this->getClassMetadataMock());
    }

    public function testIncorrectDirectory()
    {
        $generatorMockBuilder = $this->getMockBuilder(DoctrineSerializationConfigGenerator::class)
            ->setConstructorArgs([$this->getBundleMock('/root/wrong/dir'), self::ENTITY]);

        $generator = $generatorMockBuilder
            ->setMethods(['writeYamlToFile', 'getYamlFileContent'])
            ->getMock();

        $this->expectException(IOException::class);

        $generator->generate($this->getClassMetadataMock());
    }

    public function testGenerateWillNotOverwrite()
    {

        $generatorMockBuilder = $this->getMockBuilder(DoctrineSerializationConfigGenerator::class)
            ->setConstructorArgs([$this->getBundleMock(), self::ENTITY]);

        $generator = $generatorMockBuilder
            ->setMethods(['canWriteToFile'])
            ->getMock();

        $generator->method('canWriteToFile')->willReturn(false);

        $this->expectException(\RuntimeException::class);

        $generator->generate($this->getClassMetadataMock());
    }

    /**
     * @param string $path
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getBundleMock($path = self::TMP_DIR)
    {
        $bundleMock = $this->getMockBuilder(Bundle::class)
            ->setMethods(['getPath', 'getNamespace'])
            ->getMock();

        $bundleMock->method('getNamespace')->willReturn(self::BUNDLE);
        $bundleMock->method('getPath')->willReturn($path);

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
                ],
                'field_3' => [
                    'id' => 'id',
                    'type' => 'date',
                    'targetEntity' => 'Pgs\DummyBundle\Entity\DummyEntity2'
                ]
            ]);
        $mock->method('isIdentifierNatural')->willReturn(false);
        $mock->method('getIdentifier')->willReturn(['field_3']);

        return $mock;
    }
}
