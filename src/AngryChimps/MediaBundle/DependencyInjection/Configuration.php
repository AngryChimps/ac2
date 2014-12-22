<?php

namespace AngryChimps\MediaBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('angry_chimps_media');

        $rootNode
            ->children()
                ->arrayNode('filesystems')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                    ->children()
                        ->scalarNode('upload_width_px')->isRequired()->end()
                        ->scalarNode('upload_height_px')->isRequired()->end()
                    ->end()
                ->end()
            ->end();


        return $treeBuilder;
    }
}
