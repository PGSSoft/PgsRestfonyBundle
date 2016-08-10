<?php

namespace Pgs\RestfonyBundle\Tests\Form\Factory;

use Pgs\RestfonyBundle\Form\Factory\RestFormFactory;
use Pgs\RestfonyBundle\Tests\Form\Dummy;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormTypeInterface;

/**
 * @covers Pgs\RestfonyBundle\Form\Factory\RestFormFactory
 */
class RestFormFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldCreateObjectForInjectedFactory()
    {
        $formFactory = $this->prophesize(FormFactoryInterface::class);
        $formFactory->create(Argument::type('string'), Argument::exact(null), Argument::exact([]))
            ->willReturn('ThisShouldBeForm');

        $formType = $this->prophesize(FormTypeInterface::class);
        $formType->formType = new Dummy();

        $restFormFactory = new RestFormFactory(
            $formFactory->reveal(),
            $formType->reveal()
        );

        $this->assertSame('ThisShouldBeForm', $restFormFactory->create());
    }
}
