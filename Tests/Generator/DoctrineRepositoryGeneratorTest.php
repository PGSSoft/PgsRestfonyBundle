<?php

namespace Pgs\RestfonyBundle\Tests\Generator;

use PHPUnit\Framework\TestCase;
use Pgs\RestfonyBundle\Generator\DoctrineRepositoryGenerator;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DoctrineRepositoryGeneratorTest extends TestCase
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
        $this->generatorMockBuilder = $this->getMockBuilder(DoctrineRepositoryGenerator::class)
            ->setConstructorArgs([ $this->getBundleMock(), self::ENTITY ]);
    }

    public function testGenerate()
    {
        $generator = $this->generatorMockBuilder
            ->setMethods([ 'renderFile' ])
            ->getMock();

        $generator
            ->expects($this->once())
            ->method('renderFile');

        $generator->generate();
    }

    public function testGenerateWillNotOverwrite()
    {
        $generator = $this->generatorMockBuilder
            ->setMethods([ 'canWriteToFile' ])
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
}
