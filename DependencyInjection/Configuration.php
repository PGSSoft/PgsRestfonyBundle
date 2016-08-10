<?php

namespace Pgs\RestfonyBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('pgs_restfony');

        $rootNode
            ->children()
                ->arrayNode('modules')
                ->prototype('array')
                    ->children()
                        ->scalarNode('controller')->isRequired()->end()
                        ->scalarNode('manager')->isRequired()->end()
                        ->scalarNode('entity')->isRequired()->end()
                        ->scalarNode('form')->end()
                        ->scalarNode('filter')->end()
                        ->arrayNode('sorts')
                            ->prototype('scalar')
                        ->end()
                    ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
