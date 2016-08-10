<?php

namespace Pgs\RestfonyBundle\Tests;

use Pgs\RestfonyBundle\DependencyInjection\Compiler\RegisterRestModulesCompilerPass;
use Pgs\RestfonyBundle\PgsRestfonyBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PgsRestBundleTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldInitializeCompilerPass()
    {
        $bundle = new PgsRestfonyBundle();
        $container = $this->getContainerMock();
        $bundle->build($container);
    }

    /**
     * @return ContainerBuilder
     */
    private function getContainerMock()
    {
        $container = $this->prophesize(ContainerBuilder::class);
        $container->addCompilerPass(new RegisterRestModulesCompilerPass())->shouldBeCalled()->willReturn($container);

        return $container->reveal();
    }
}
