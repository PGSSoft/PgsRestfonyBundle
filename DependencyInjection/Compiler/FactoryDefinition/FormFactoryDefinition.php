<?php

namespace Pgs\RestfonyBundle\DependencyInjection\Compiler\FactoryDefinition;

use Pgs\RestfonyBundle\Form\Factory\RestFormFactory;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Michał Sikora
 */
class FormFactoryDefinition
{
    /**
     * @param string $moduleName
     *
     * @return Definition
     */
    public function create($moduleName)
    {
        $formFactoryDefinition = new Definition();
        $formFactoryDefinition->setClass(RestFormFactory::class);
        $formFactoryDefinition->setArguments([
            new Reference('form.factory'),
            new Reference('pgs.rest.form.'.$moduleName),
        ]);

        return $formFactoryDefinition;
    }
}
