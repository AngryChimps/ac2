<?php

namespace AC\NormBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use AC\NormBundle\core\datastore\DatastoreManager;
use AC\NormBundle\core\generator\Generator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use AC\NormBundle\DependencyInjection\Compiler\ValidatorPass;
class ACNormBundle extends Bundle
{
    /**
     * Boots the Bundle.
     */
    public function boot()
    {
        foreach($this->container->getParameter('ac_norm.realms') as $realmName => $realmInfo) {
            spl_autoload_register(function ($class) use ($realmName, $realmInfo) {
                if(strpos($class, $realmInfo['namespace']) === 0) {
                    require_once(__DIR__ . '/../../../../../app/cache/'
                        . $this->container->get('kernel')->getEnvironment() . '/angrychimps/norm/realms/'
                        . $realmName . '/classes/classes.php');
                }
            });
        }

        //Set up autoloader for cached services
        spl_autoload_register(function ($class) {
            if(strpos($class, 'AC\\NormBundle\\cached') === 0) {
                $class_parts = explode('\\', $class);
                require_once(__DIR__ . '/../../../../../app/cache/'
                    . $this->container->get('kernel')->getEnvironment() . '/angrychimps/norm/'
                    . implode("/", array_slice($class_parts, 3)) . '.php');
            }
            elseif(strpos($class, 'AC\\NormBundle') === 0) {
                $class_parts = explode('\\', $class);
                require_once(__DIR__ . '/../../' . implode('/', $class_parts) . '.php');
            }
        });
    }

    public function build(ContainerBuilder $container) {
        parent::build($container);

        $container->addCompilerPass(new ValidatorPass());
    }
}
