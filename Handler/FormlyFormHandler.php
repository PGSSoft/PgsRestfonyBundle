<?php

namespace Pgs\RestfonyBundle\Handler;

use JMS\Serializer\GenericSerializationVisitor;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

class FormlyFormHandler implements SubscribingHandlerInterface
{
    private $translator;
    /**
     * @var RouterInterface
     */
    private $router;

    public static function getSubscribingMethods()
    {
        $methods = array();
        foreach (array('xml', 'json', 'yml') as $format) {
            $methods[] = array(
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'type' => Form::class,
                'format' => $format,
            );
            $methods[] = array(
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'type' => FormError::class,
                'format' => $format,
            );
        }

        return $methods;
    }

    public function __construct(TranslatorInterface $translator, RouterInterface $router)
    {
        $this->translator = $translator;
        $this->router = $router;
    }

    public function serializeFormToJson(JsonSerializationVisitor $visitor, Form $form)
    {
        return $this->convertFormToArray($visitor, $form);
    }

    protected function getErrorMessage(FormError $error)
    {
        if (null !== $error->getMessagePluralization()) {
            return $this->translator->transChoice(
                $error->getMessageTemplate(),
                $error->getMessagePluralization(),
                $error->getMessageParameters(),
                'validators'
            );
        }

        return $this->translator->trans($error->getMessageTemplate(), $error->getMessageParameters(), 'validators');
    }

    protected function convertFormToArray(GenericSerializationVisitor $visitor, Form $data)
    {
        $isRoot = null === $visitor->getRoot();

        $form = $errors = array();
        foreach ($data->getErrors() as $error) {
            $errors[] = $this->getErrorMessage($error);
        }

        if ($errors) {
            $form['errors'] = $errors;
        }

        $children = array();
        foreach ($data->all() as $child) {
            if ($child instanceof Form) {
                $children[$child->getName()] = $this->getConvertedChild($child, $visitor);
            }
        }

        if ($children) {
            $form['children'] = $children;
        }

        if ($isRoot) {
            $visitor->setRoot($form);
        }

        return $form;
    }

    /**
     * @param Form $child
     * @param GenericSerializationVisitor $visitor
     * @return array
     */
    private function getConvertedChild(Form $child, GenericSerializationVisitor $visitor)
    {
        if ($child->count()) {
            return [
                'type' => 'multifield',
                'key' => $child->getName(),
                'templateOptions' => [
                    'fields' => $this->convertFormToArray($visitor, $child),
                ],
            ];
        } else {
            $templateOptions = [
                'label' => $child->getConfig()->getOption('label'),
                'required' => $child->isRequired(),
            ];

            if ($child->getConfig()->getOption('custom-type')) {
                $textType = $child->getConfig()->getOption('custom-type');
            } else {
                $type = $child->getConfig()->getType();
                switch ($type->getName()) {
                    case 'rest_entity':
                        $textType = 'select-one';
                        $templateOptions['autocompleteUrl']
                            = $this->getAutocompleteUrl($child->getConfig()->getDataClass());
                        break;
                    case 'rest_collection':
                        $textType = 'select-multi';
                        $templateOptions['autocompleteUrl']
                            = $this->getAutocompleteUrl($child->getConfig()->getDataClass());
                        break;
                    default:
                        $textType = 'input';
                        break;
                }
            }

            return [
                'type' => $textType,
                'key' => $child->getName(),
                'templateOptions' => $templateOptions,
            ];
        }
    }

    protected function getAutocompleteUrl($dataclass)
    {
        $entityName = $dataclass;

        $routeName = 'autocomplete_' . strtolower($entityName);

        return $this->router->getRouteCollection()->get($routeName) ? $this->router->generate($routeName) : null;
    }
}
