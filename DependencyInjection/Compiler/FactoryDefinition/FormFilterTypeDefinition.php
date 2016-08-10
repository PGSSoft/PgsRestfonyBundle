<?php

namespace Pgs\RestfonyBundle\DependencyInjection\Compiler\FactoryDefinition;

use Symfony\Component\DependencyInjection\Definition;

/**
 * @author Michał Sikora
 */
class FormFilterTypeDefinition
{
    /**
     * @param string $moduleName
     * @param string $formFilterTypeClass
     *
     * @return Definition
     */
    public function create($moduleName, $formFilterTypeClass)
    {
        $formFilterDefinition = new Definition();
        $formFilterDefinition->setClass($formFilterTypeClass);
        $formFilterDefinition->setArguments([]);
        $formFilterDefinition->addTag('form.type', ['alias' => $moduleName.'_filter']);

        return $formFilterDefinition;
    }
}
