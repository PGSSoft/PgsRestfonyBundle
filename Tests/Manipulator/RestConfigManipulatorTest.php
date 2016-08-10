<?php

namespace Tests\Manipulator;

use Doctrine\ORM\Mapping\ClassMetadata;
use Pgs\RestfonyBundle\Manipulator\RestConfigManipulator;
use PHPUnit\Framework\TestCase;
use phpmock\phpunit\PHPMock;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;

/**
 * @covers Pgs\RestfonyBundle\Manipulator\RestConfigManipulator
 */
class RestConfigManipulatorTest extends TestCase
{
    use PHPMock;

    /**
     * @test
     */
    public function throwRuntimeExceptionWhenFileDoesNotExist()
    {
        $restConfigManipulator = new RestConfigManipulator('/absolutely/not/a/file/path');

        $this->expectException(RuntimeException::class);

        $restConfigManipulator->addResource('DummyBundle', 'DummyEntity', []);
    }

    /**
     * @test
     */
    public function returnFalseWhenCannotSaveFile()
    {
        file_put_contents($this->getDummyFilePath(), '');

        $mock = $this->getFunctionMock('Pgs\RestfonyBundle\Manipulator', 'file_put_contents');
        $mock->expects($this->any())->will($this->returnValue(false));

        $restConfigManipulator = new RestConfigManipulator($this->getDummyFilePath());

        $classMetadata = new ClassMetadata('42');
        $classMetadata->addInheritedFieldMapping([
            'columnName' => 'column',
            'fieldName' => 'field',
        ]);

        $this->assertFalse($restConfigManipulator->addResource('DummyBundle', '42', [$classMetadata]));

        unlink($this->getDummyFilePath());
    }

    /**
     * @test
     */
    public function returnTrueWhenResourceWasAdded()
    {
        file_put_contents($this->getDummyFilePath(), Yaml::dump(['pgs_restfony' => [
            'modules' => [
                'entity' => 'DummyBundle\EntityDummyEntity',
                'form' => 'DummyBundleForm\TypeDummyEntityType',
                'filter' => 'DummyBundleForm\FilterDummyEntityFilterType',
                'controller' => 'DummyBundleControllerDummyEntityController',
                'manager' => 'DummyBundleManagerDummyEntityManager',
            ],
        ]]));

        $restConfigManipulator = new RestConfigManipulator($this->getDummyFilePath());

        $this->assertTrue($restConfigManipulator->addResource('DummyBundle', 'TestingEntity', [new ClassMetadata('TestingEntity')]));

        $yaml = Yaml::parse(file_get_contents($this->getDummyFilePath()));

        $this->assertArrayHasKey('pgs_restfony', $yaml);
        $this->assertArrayHasKey('modules', $yaml['pgs_restfony']);
        $this->assertArrayHasKey('testingentity', $yaml['pgs_restfony']['modules']);

        unlink($this->getDummyFilePath());
    }

    /**
     * @return string
     */
    private function getDummyFilePath()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'dummyFile.yml';
    }
}
