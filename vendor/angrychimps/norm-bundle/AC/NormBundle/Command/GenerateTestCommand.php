<?php


namespace AC\NormBundle\Command;

use AC\NormBundle\core\generator\Generator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateTestCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('norm:generate_test')
            ->setDescription('Generate objects for all test realms')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach($this->getRealmNames() as $realmName) {
            $realmInfo = $this->getRealmInfo($realmName);

            $ds_contents = file_get_contents(__DIR__ . '/../Tests/config/ac_norm_test.yml');
            $ds_parsed = yaml_parse($ds_contents);
            $realms_contents = file_get_contents(__DIR__ . '/../Tests/config/ac_norm.yml');
            $realms_parsed = yaml_parse($realms_contents);


            Generator::setDatastores($ds_parsed['ac_norm']['datastores']);
            Generator::setRealms($realms_parsed['ac_norm']['realms']);
            $generator = new Generator();
            $generator->generate($realmName, $realmInfo, true);

            $output->writeln("\n\n=========\n\nGenerated objects for realm: $realmName\n");
        }
    }

    protected function getRealmInfo($realmName) {
        $contents = file_get_contents(__DIR__ . "/../Tests/config/ac_norm.yml");
        $parsed = yaml_parse($contents);

        return $parsed['ac_norm']['realms'][$realmName];
    }

    protected function getRealmNames() {
        $contents = file_get_contents(__DIR__ . "/../Tests/config/ac_norm.yml");
        $parsed = yaml_parse($contents);

        $realmNames = array();
        foreach($parsed['ac_norm']['realms'] as $realmName => $realmInfo) {
            $realmNames[] = $realmName;
        }

        return $realmNames;
    }
}
