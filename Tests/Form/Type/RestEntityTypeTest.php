<?php

namespace Pgs\RestfonyBundle\Tests\Form\Type;

use Doctrine\Bundle\DoctrineCacheBundle\Tests\TestCase;
use Doctrine\Common\Persistence\ObjectManager;
use Pgs\RestfonyBundle\Form\Type\RestEntityType;
use Prophecy\Argument;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @covers Pgs\RestfonyBundle\Form\Type\RestEntityType
 */
class RestEntityTypeTest extends TestCase
{
    /** @var RestEntityType */
    private $restEntityType;

    protected function setUp()
    {
        $objectManager = $this->prophesize(ObjectManager::class)->reveal();

        $this->restEntityType = new RestEntityType($objectManager);
    }

    /**
     * @test
     */
    public function buildForm()
    {
        $formBuilderInterface = $this->prophesize(FormBuilderInterface::class);

        $formBuilderInterface->addModelTransformer(Argument::any())->shouldBeCalled();

        $this->restEntityType->buildForm($formBuilderInterface->reveal(), ['entityName' => 'Dummy']);
    }

    /**
     * @test
     */
    public function configureOptions()
    {
        $resolver = new OptionsResolver();

        $this->restEntityType->configureOptions($resolver);

        $options = $resolver->resolve(['entityName' => 'Dummy']);

        $this->assertInternalType('array', $options);
        $this->assertArrayHasKey('invalid_message', $options);
        $this->assertStringStartsWith('This value is not valid.', $options['invalid_message']);
    }
}
