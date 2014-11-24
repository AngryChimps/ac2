<?php


namespace AC\NormBundle\Command;

use AC\NormBundle\core\generator\Generator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('norm:generate')
            ->setDescription('Generate objects for a specific realm')
            ->addArgument(
                'realm',
                InputArgument::REQUIRED,
                'What is the name of the realm to generate?'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $realmName = $input->getArgument('realm');
        $realmInfo = $this->getRealmInfo($realmName);

        $generator = new Generator();
        $generator->generate($realmName, $realmInfo);

        $output->writeln("\n\n=========\n\nGenerated objects for realm: $realmName\n");
    }

    protected function getRealmInfo($realmName) {
        $contents = file_get_contents(__DIR__ . "/../../../../../../app/config/ac_norm.yml");
        $parsed = yaml_parse($contents);

        return $parsed['realms'][$realmName];
    }
}
