<?php

namespace Pgs\RestfonyBundle\DependencyInjection\Compiler\FactoryDefinition;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Michał Sikora
 */
class ManagerDefinition
{
    /**
     * @param string $managerClass
     * @param string $entityClass
     *
     * @return Definition
     */
    public function create($managerClass, $entityClass)
    {
        $managerDefinition = new Definition();
        $managerDefinition->setClass($managerClass);
        $managerDefinition->setArguments([
            new Reference('doctrine.orm.default_entity_manager'),
            $entityClass,
        ]);

        return $managerDefinition;
    }
}
