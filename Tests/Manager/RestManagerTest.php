<?php

namespace Pgs\RestfonyBundle\Tests\Manager;

use PHPUnit\Framework\TestCase;
use stdClass;

class RestManagerTest extends TestCase
{
    /**
     *  @test
     */
    public function itReturnsNullForNonExistingEntity()
    {
        $repository = $this->prophesize(RestRepositoryMock::class);
        $repository->find(1)->willReturn(null);
        $manager = new RestManagerMock($repository->reveal());

        $this->assertNull($manager->find(1), 'Result not equal to null!');
    }

    /**
     *  @test
     */
    public function itReturnsEntityForExistingEntity()
    {
        $result = new stdClass();

        $repository = $this->prophesize(RestRepositoryMock::class);
        $repository->find(1)->willReturn($result);
        $manager = new RestManagerMock($repository->reveal());

        $this->assertEquals($result, $manager->find(1), 'Result equal to given object!');
    }
}
