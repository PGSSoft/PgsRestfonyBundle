<?php

namespace Pgs\RestfonyBundle\DependencyInjection\Compiler;

use Pgs\RestfonyBundle\DependencyInjection\Compiler\FactoryDefinition\ControllerFactoryDefinition;
use Pgs\RestfonyBundle\DependencyInjection\Compiler\FactoryDefinition\FilterFactoryDefinition;
use Pgs\RestfonyBundle\DependencyInjection\Compiler\FactoryDefinition\FormFactoryDefinition;
use Pgs\RestfonyBundle\DependencyInjection\Compiler\FactoryDefinition\FormFilterTypeDefinition;
use Pgs\RestfonyBundle\DependencyInjection\Compiler\FactoryDefinition\FormTypeDefinition;
use Pgs\RestfonyBundle\DependencyInjection\Compiler\FactoryDefinition\ManagerDefinition;
use Pgs\RestfonyBundle\DependencyInjection\Compiler\FactoryDefinition\RestManagerDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Michał Sikora
 */
class RegisterRestModulesCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     * @return bool
     */
    public function process(ContainerBuilder $container)
    {
        $config = $container->getExtensionConfig('pgs_restfony')[0];

        if (!isset($config['modules'])) {
            return false;
        }

        foreach ($config['modules'] as $moduleName => $module) {
            $serviceAlias = 'pgs.rest.manager.'.$moduleName;
            if (!$container->hasDefinition($serviceAlias)) {
                $managerDefinition = (new ManagerDefinition())->create($module['manager'], $module['entity']);
                $container->setDefinition($serviceAlias, $managerDefinition);
            }

            $serviceAlias = 'pgs.rest.form.'.$moduleName;
            if (!$container->hasDefinition($serviceAlias)) {
                $formDefinition = (new FormTypeDefinition())->create($moduleName, $module['form']);
                $container->setDefinition($serviceAlias, $formDefinition);
            }

            $serviceAlias = 'pgs.rest.form_filter.'.$moduleName;
            if (!$container->hasDefinition($serviceAlias)) {
                $formFilterDefinition = (new FormFilterTypeDefinition())->create($moduleName, $module['filter']);
                $container->setDefinition($serviceAlias, $formFilterDefinition);
            }

            $serviceAlias = 'pgs.rest.form_factory.'.$moduleName;
            if (!$container->hasDefinition($serviceAlias)) {
                $formFactoryDefinition = (new FormFactoryDefinition())->create($moduleName);
                $container->setDefinition($serviceAlias, $formFactoryDefinition);
            }

            $serviceAlias = 'pgs.rest.filter_factory.'.$moduleName;
            if (!$container->hasDefinition($serviceAlias)) {
                $formFactoryDefinition = (new FilterFactoryDefinition())->create($moduleName);
                $container->setDefinition($serviceAlias, $formFactoryDefinition);
            }

            $serviceAlias = 'pgs.rest.rest_manager.'.$moduleName;
            if (!$container->hasDefinition($serviceAlias)) {
                $restManagerDefinition = (new RestManagerDefinition())->create($moduleName);
                $container->setDefinition($serviceAlias, $restManagerDefinition);
            }

            $serviceAlias = 'pgs.rest.controller.'.$moduleName;
            if (!$container->hasDefinition($serviceAlias)) {
                $controllerDefinition = (new ControllerFactoryDefinition())->create(
                    $moduleName,
                    $module['controller'],
                    $module['sorts']
                );
                $container->setDefinition($serviceAlias, $controllerDefinition);
            }
        }

        return true;
    }
}
