<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new AngryChimps\AcBundle\AngryChimpsAcBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new AC\NormBundle\ACNormBundle(),
//            new Test\TesterBundle\TestTesterBundle(),
            new AngryChimps\ApiBundle\AngryChimpsApiBundle(),
//            new AngryChimps\SecurityBundle\AngryChimpsSecurityBundle(),
//            new FOS\FacebookBundle\FOSFacebookBundle(),
//            new Armetiz\FacebookBundle\ArmetizFacebookBundle(),
//            new Test\DirectoryStructureBundle\TestDirectoryStructureBundle(),
            new AngryChimps\AdminBundle\AngryChimpsAdminBundle(),
            new AngryChimps\MailerBundle\AngryChimpsMailerBundle(),
            new AngryChimps\GeoBundle\AngryChimpsGeoBundle(),
            new AngryChimps\GuzzleBundle\AngryChimpsGuzzleBundle(),
            new AngryChimps\ElasticsearchBundle\AngryChimpsElasticsearchBundle(),
            new AngryChimps\SampleBundle\AngryChimpsSampleBundle(),
            new AngryChimps\MediaBundle\AngryChimpsMediaBundle(),
            new Knp\Bundle\GaufretteBundle\KnpGaufretteBundle(),
            new AngryChimps\TaskBundle\AngryChimpsTaskBundle(),
        );

        if (true || in_array($this->getEnvironment(), array('dev', 'test', 'local', 'behat', 'behat_dev'))) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
