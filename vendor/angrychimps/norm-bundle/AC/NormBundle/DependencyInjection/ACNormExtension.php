<?php

namespace AC\NormBundle\DependencyInjection;

use AC\NormBundle\core\datastore\DatastoreManager;
use AC\NormBundle\core\generator\Generator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class ACNormExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

//        $yamlMappingFiles = array();
//        $yamlMappingFiles = $container->getParameter('validator.mapping.loader.yaml_files_loader.mapping_files');
//        $yamlMappingFiles[] = __DIR__.'/../Resources/config/validation.yml';
//        $container->setParameter('validator.mapping.loader.yaml_files_loader.mapping_files', $yamlMappingFiles);
//        $builderDefinition = $container->getDefinition('validator.builder');
//        $builderDefinition->addMethodCall('addYamlMappings', array(
//                array(
//                    __DIR__.'/../Resources/config/validation.yml'
//                )
//            ));
    }
}
