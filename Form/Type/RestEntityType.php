<?php

namespace Pgs\RestfonyBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;
use Doctrine\Common\Persistence\ObjectManager;
use Pgs\RestfonyBundle\Form\DataTransformer\RestEntityTransformer;

class RestEntityType extends AbstractType
{
    /** @var ObjectManager */
    private $objectManager;

    /**
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new RestEntityTransformer($this->objectManager, $options['entityName']);
        $builder->addModelTransformer($transformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['entityName']);

        if ($resolver instanceof OptionsResolver) {
            $resolver->setAllowedTypes('entityName', ['string']);
            $resolver->setDefined(['entityName']);
            $resolver->setDefault('invalid_message', function (Options $options) {
                return 'This value is not valid. Unable to find ' . $options['entityName'] . ' in the database.';
            });
        }
    }
}
