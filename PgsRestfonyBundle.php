<?php

namespace Pgs\RestfonyBundle;

use Pgs\RestfonyBundle\DependencyInjection\Compiler\RegisterRestModulesCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Michał Sikora
 */
class PgsRestfonyBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new RegisterRestModulesCompilerPass());
    }
}
