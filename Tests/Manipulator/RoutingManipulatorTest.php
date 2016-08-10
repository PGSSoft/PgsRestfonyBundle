<?php

namespace Tests\Manipulator;

use Pgs\RestfonyBundle\Manipulator\RoutingManipulator;
use PHPUnit\Framework\TestCase;

/**
 * @covers Pgs\RestfonyBundle\Manipulator\RoutingManipulator
 */
class RoutingManipulatorTest extends TestCase
{
    /**
     * @test
     */
    public function allowToAddResourceExactlyOnce()
    {
        $restConfigManipulator = new RoutingManipulator($this->getDummyFilePath());

        $this->assertTrue($restConfigManipulator->addResource('DummyBundle'));
        $this->assertTrue($restConfigManipulator->addResource('DummyBundle', 'Prefix'));
        $this->assertFalse($restConfigManipulator->addResource('DummyBundle', 'Prefix'));

        unlink($this->getDummyFilePath());
        rmdir(dirname($this->getDummyFilePath()));
    }

    /**
     * @return string
     */
    private function getDummyFilePath()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'dummyFile.yml';
    }
}
