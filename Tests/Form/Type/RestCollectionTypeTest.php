<?php

namespace Pgs\RestfonyBundle\Tests\Form\Type;

use Doctrine\Bundle\DoctrineCacheBundle\Tests\TestCase;
use Doctrine\Common\Persistence\ObjectManager;
use Pgs\RestfonyBundle\Form\Type\RestCollectionType;
use Prophecy\Argument;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @covers Pgs\RestfonyBundle\Form\Type\RestCollectionType
 */
class RestCollectionTypeTest extends TestCase
{
    /** @var RestCollectionType */
    private $restCollectionType;

    protected function setUp()
    {
        $objectManager = $this->prophesize(ObjectManager::class)->reveal();

        $this->restCollectionType = new RestCollectionType($objectManager);
    }

    /**
     * @test
     */
    public function buildForm()
    {
        $formBuilderInterface = $this->prophesize(FormBuilderInterface::class);

        $formBuilderInterface->addModelTransformer(Argument::any())->shouldBeCalled();
        $formBuilderInterface->addViewTransformer(Argument::any())->shouldBeCalled();

        $this->restCollectionType->buildForm($formBuilderInterface->reveal(), ['entityName' => 'Dummy']);
    }

    /**
     * @test
     */
    public function configureOptions()
    {
        $resolver = new OptionsResolver();

        $this->restCollectionType->configureOptions($resolver);

        $options = $resolver->resolve(['entityName' => 'Dummy']);

        $this->assertInternalType('array', $options);
        $this->assertArrayHasKey('invalid_message', $options);
        $this->assertStringStartsWith('This value is not valid.', $options['invalid_message']);
    }
}
