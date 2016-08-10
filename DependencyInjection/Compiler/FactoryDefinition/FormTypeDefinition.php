<?php

namespace Pgs\RestfonyBundle\DependencyInjection\Compiler\FactoryDefinition;

use Symfony\Component\DependencyInjection\Definition;

/**
 * @author Michał Sikora
 */
class FormTypeDefinition
{
    /**
     * @param string $moduleName
     * @param string $formTypeClass
     *
     * @return Definition
     */
    public function create($moduleName, $formTypeClass)
    {
        $formDefinition = new Definition();
        $formDefinition->setClass($formTypeClass);
        $formDefinition->setArguments([]);
        $formDefinition->addTag('form.type', ['alias' => $moduleName]);

        return $formDefinition;
    }
}
