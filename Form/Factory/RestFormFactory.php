<?php

namespace Pgs\RestfonyBundle\Form\Factory;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;

/**
 * @author Michał Sikora
 */
class RestFormFactory
{
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var FormTypeInterface
     */
    private $formType;

    /**
     * @param FormFactoryInterface $formFactory
     * @param FormTypeInterface    $formType
     */
    public function __construct(FormFactoryInterface $formFactory, FormTypeInterface $formType)
    {
        $this->formFactory = $formFactory;
        $this->formType = $formType;
    }

    /**
     * @param object $data
     * @param array  $options
     *
     * @return FormInterface
     */
    public function create($data = null, array $options = array())
    {
        $formTypeClass = get_class($this->formType);
        return $this->formFactory->create($formTypeClass, $data, $options);
    }
}
