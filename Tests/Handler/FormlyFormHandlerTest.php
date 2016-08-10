<?php

namespace Pgs\RestfonyBundle\Tests\Handler;

use JMS\Serializer\JsonSerializationVisitor;
use Pgs\RestfonyBundle\Handler\FormlyFormHandler;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @covers Pgs\RestfonyBundle\Handler\FormlyFormHandler
 */
class FormlyFormHandlerTest extends TestCase
{
    /** @var FormlyFormHandler */
    private $formlyFormHandler;

    protected function setUp()
    {
        $router = $this->prophesize(RouterInterface::class);
        $router->getRouteCollection()->willReturn(new RouteCollection());

        $this->formlyFormHandler = new FormlyFormHandler(
            $this->prophesize(TranslatorInterface::class)->reveal(),
            $router->reveal()
        );
    }

    /**
     * @test
     */
    public function correctSubscribingMethods()
    {
        foreach ($this->formlyFormHandler->getSubscribingMethods() as $subscribingMethods) {
            $this->assertArrayHasKey('direction', $subscribingMethods);

            $this->assertArrayHasKey('type', $subscribingMethods);
            $this->assertTrue(class_exists($subscribingMethods['type']));

            $this->assertArrayHasKey('format', $subscribingMethods);
        }
    }

    /**
     * @return array
     */
    public function serializeFormToJsonProvider()
    {
        return [
            [true, 'rest_entity'],
            [false, 'rest_entity'],
            [false, 'rest_collection'],
            [false, 'something_else'],
        ];
    }

    /**
     * @dataProvider serializeFormToJsonProvider
     * @param bool $isCustomType
     * @param string $typeName
     * @test
     */
    public function serializeFormToJson($isCustomType, $typeName)
    {
        $jsonSerializationVisitor = $this->prophesize(JsonSerializationVisitor::class)->reveal();

        $childForm = new Form($this->getConfigMock($isCustomType, $typeName));
        $childForm->add(new Form($this->getConfigMock($isCustomType, $typeName)));

        $form = new Form($this->getConfigMock($isCustomType, $typeName));
        $form->addError(new FormError('Dummy error'));
        $form->addError(new FormError('Dummy error2', null, [], 4));
        $form->add($childForm);

        $this->assertInternalType('array', $this->formlyFormHandler->serializeFormToJson($jsonSerializationVisitor, $form));
    }

    /**
     * @param bool $isCustomType
     * @param string $typeName
     * @return FormConfigInterface
     */
    private function getConfigMock($isCustomType, $typeName)
    {
        $formConfigInterface = $this->prophesize(FormConfigInterface::class);
        $formConfigInterface->getAutoInitialize()->willReturn(false);
        $formConfigInterface->getCompound()->willReturn(true);
        $formConfigInterface->getDataClass()->willReturn('DataClass');
        $formConfigInterface->getDataMapper()->willReturn(true);
        $formConfigInterface->getInheritData()->willReturn(true);
        $formConfigInterface->getName()->willReturn(true);
        $formConfigInterface->getOption('custom-type')->willReturn($isCustomType);
        $formConfigInterface->getOption('label')->willReturn(true);
        $formConfigInterface->getRequired()->willReturn(true);
        $formConfigInterface->getType()->willReturn(new DummyType($typeName));
        return $formConfigInterface->reveal();
    }
}
