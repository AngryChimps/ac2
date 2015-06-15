<?php

namespace AC\NormBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use AC\NormBundle\DependencyInjection\Compiler\ValidatorPass;
use AC\NormBundle\Services\CreatorService;

class ACNormBundle extends Bundle
{
    /**
     * Boots the Bundle.
     */
    public function boot()
    {
       spl_autoload_register(function($class) {
            if(strpos($class, $this->container->getParameter('ac_norm.namespace')) === 0) {
                require_once(__DIR__ . '/../../../../../app/cache/'
                    . $this->container->get('kernel')->getEnvironment() . '/norm/classes.php');
            }
        });

        //Set up autoloader for cached services
        spl_autoload_register(function ($class) {
            if(strpos($class, 'AC\\NormBundle\\cached') === 0) {
                if(!file_exists(__DIR__ . '/../../../../../app/cache/'
                        . $this->container->get('kernel')->getEnvironment() . '/norm/classes.php')) {

                    /** @var CreatorService $cs */
                    $cs = $this->container->get('ac_norm.creator');
                    $cs->createIfNecessary();
                }
                require_once(__DIR__ . '/../../../../../app/cache/'
                    . $this->container->get('kernel')->getEnvironment() . '/norm/classes.php');
                require_once(__DIR__ . '/../../../../../app/cache/'
                    . $this->container->get('kernel')->getEnvironment() . '/norm/NormBaseService.php');
                require_once(__DIR__ . '/../../../../../app/cache/'
                    . $this->container->get('kernel')->getEnvironment() . '/norm/structure.php');
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
