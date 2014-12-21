<?php

namespace AC\NormBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ac_norm');

        $rootNode
            ->children()
                ->scalarNode('debug')->end()
                ->arrayNode('realms')
                    ->children()
                        ->scalarNode('name')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('namespace')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('primary_datastore')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
            ->end();
//                ->end()
//                ->arrayNode('datastores')
//                    ->children()
//                        ->scalarNode('name')
//                            ->isRequired()
//                            ->cannotBeEmpty()
//                        ->end()
//                        ->scalarNode('driver')
//                            ->isRequired()
//                            ->cannotBeEmpty()
//                        ->end()
//                        ->scalarNode('host')->end()
//                        ->scalarNode('port')->end()
//                        ->scalarNode('db_name')->end()
//                        ->scalarNode('user')->end()
//                        ->scalarNode('password')->end()
//                        ->scalarNode('index_name')->end()
//                        ->arrayNode('servers')
//                            ->children()
//                                ->scalarNode('host')->end()
//                                ->scalarNode('port')->end()
//                            ->end()
//                        ->end()
//                    ->end()
//                ->end()
//            ->end()
//        ;

        return $treeBuilder;
    }
}
