<?php

namespace AngryChimps\GeoBundle\DependencyInjection;

use AngryChimps\GeoBundle\Services\GeolocationService;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class AngryChimpsGeoExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('angry_chimps_geo.google_api_key', $config['google_api_key']);
        $container->setParameter('angry_chimps_geo.google_maps_api_address', $config['google_maps_api_address']);
        $container->setParameter('angry_chimps_geo.google_maps_time_address', $config['google_maps_time_address']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
