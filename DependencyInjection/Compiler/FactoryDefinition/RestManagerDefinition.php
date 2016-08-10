<?php

namespace Pgs\RestfonyBundle\DependencyInjection\Compiler\FactoryDefinition;

use Pgs\RestfonyBundle\Controller\RestManager;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Michał Sikora
 */
class RestManagerDefinition
{
    /**
     * @param string $moduleName
     *
     * @return Definition
     */
    public function create($moduleName)
    {
        $managerDefinition = new Definition();
        $managerDefinition->setClass(RestManager::class);
        $managerDefinition->setArguments([
            $moduleName,
            new Reference('pgs.rest.manager.'.$moduleName),
            new Reference('pgs.rest.form_factory.'.$moduleName),
            new Reference('pgs.rest.filter_factory.'.$moduleName),
        ]);

        return $managerDefinition;
    }
}
