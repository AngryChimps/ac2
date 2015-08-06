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
                ->scalarNode('namespace')->end()
                ->arrayNode('defaults')
                    ->children()
                        ->arrayNode('riak2')
                            ->children()
                                ->booleanNode('indexed')->end()
                            ->end()
                        ->end()
                        ->arrayNode('elasticsearch')
                            ->children()
                                ->booleanNode('indexed')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('datastores')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                    ->children()
                        ->scalarNode('driver')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('prefix')->end()
                        ->scalarNode('host')->end()
                        ->scalarNode('port')->end()
                        ->scalarNode('db_name')->end()
                        ->scalarNode('user')->end()
                        ->scalarNode('password')->end()
                        ->scalarNode('index_name')->end()
                        ->scalarNode('default_analyzer')->end()
                        ->scalarNode('shards')->end()
                        ->scalarNode('replicas')->end()
                        ->arrayNode('servers')
                            ->prototype('array')
                            ->children()
                                ->scalarNode('host')->end()
                                ->scalarNode('port')->end()
                                ->scalarNode('http_port')->end()
                            ->end()
                        ->end()
                    ->end();

        return $treeBuilder;
    }
}
