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
        // Merges configs and removes unnecessary array levels to yield something similiar to this:
        $config = $this->getConfig();

        DatastoreManager::setDatastores($config['datastores']);

//        Generator::setRealms($config['realms']);
//        Generator::setDatastores($config['datastores']);

        //Set up autoloaders for each realm
        foreach($config['realms'] as $realmName => $realmInfo) {
            spl_autoload_register(function ($class) use ($realmName, $realmInfo) {
                if(strpos($class, $realmInfo['namespace']) === 0) {
                    $class_parts = explode('\\', $class);
                    require_once(__DIR__ . '/../../../../../src/AngryChimps/NormBundle/realms/' . $realmName
                        . '/' . $class_parts[count($class_parts) - 1]
                        . '.php');
                }
            });
        }

        //Set up autoloader for general Norm classes
        spl_autoload_register(function ($class) {
            if(strpos($class, 'AC\\NormBundle') === 0) {
                $class_parts = explode('\\', $class);
                require_once(__DIR__ . '/../../../' . implode('/', $class_parts) . '.php');
            }
        });

        //Set up autoloader for Handlebars
        spl_autoload_register(function ($class) {
            if(strpos($class, 'Handlebars') === 0) {
                $class_parts = explode('\\', $class);
                require_once(__DIR__ . '/../vendor/xamin/handlebars.php/src/' . implode($class_parts, DIRECTORY_SEPARATOR) . '.php');
            }
        });
    }

    public function build(ContainerBuilder $container) {
        parent::build($container);

        $container->addCompilerPass(new ValidatorPass());
    }

    protected function getConfig() {
        $env = $this->container->get('kernel')->getEnvironment();

        $config = array();
        $filenames = array('ac_norm.yml', 'ac_norm_' . $env . '.yml');

        foreach($filenames as $filename){
            $yaml = file_get_contents(__DIR__ . '/../../../../../app/config/' . $filename);
            $arr = yaml_parse($yaml);
            $config = array_merge($config, $arr);
        }

        return $config;
    }
}
