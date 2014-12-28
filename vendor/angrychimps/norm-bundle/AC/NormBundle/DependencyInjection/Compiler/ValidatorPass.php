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
        $realms = array('riak', 'mysql', 'es');

        foreach($realms as $realm) {
            foreach ($finder->files()->in(__DIR__ . "/../../../../../../../app/cache/" . $container->getParameter('kernel.environment')
                . "/angrychimps/norm/realms/$realm/validations") as $file) {
                try {
                    $validatorFiles[] = $file->getRealPath();
                }
                catch(\Exception $ex) {
                    //do nothing
                }
            }
        }

        $validatorBuilder->addMethodCall('addYamlMappings', array($validatorFiles));
    }
}