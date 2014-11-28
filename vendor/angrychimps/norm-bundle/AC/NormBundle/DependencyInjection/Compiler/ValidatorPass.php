<?php


namespace AC\NormBundle\DependencyInjection\Compiler;

use Symfony\Component\Finder\Finder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class ValidatorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $validatorBuilder = $container->getDefinition('validator.builder');
        $validatorFiles = array();
        $finder = new Finder();
        //TODO: Fix this!
        $realms = array('riak', 'mysql');

        foreach($realms as $realm) {
            foreach ($finder->files()->in(__DIR__ . "/../../../../../../../src/AngryChimps/NormBundle/realms/Norm/$realm/validations") as $file) {
                $validatorFiles[] = $file->getRealPath();
            }
        }

        $validatorBuilder->addMethodCall('addYamlMappings', array($validatorFiles));
    }
}