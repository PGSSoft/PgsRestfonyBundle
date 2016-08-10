<?php

namespace Pgs\RestfonyBundle\DependencyInjection\Compiler\FactoryDefinition;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Michał Sikora
 */
class ControllerFactoryDefinition
{
    /**
     * @param string $moduleName
     * @param string $controllerClass
     *
     * @return Definition
     */
    public function create($moduleName, $controllerClass, $sorts)
    {
        $controllerDefinition = new Definition();
        $controllerDefinition->setClass($controllerClass);
        $controllerDefinition->setArguments([
            new Reference('pgs.rest.rest_manager.'.$moduleName),
            new Reference('pgs.rest.doctrine.filter_query_builder'),
            new Reference('pgs.rest.paginator_factory'),
            new Reference('event_dispatcher'),
            new Reference('fos_rest.view_handler'),
        ]);
        $controllerDefinition->addMethodCall('setSortConfiguration', [$sorts]);

        return $controllerDefinition;
    }
}
