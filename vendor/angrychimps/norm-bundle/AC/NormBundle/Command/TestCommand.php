<?php


namespace AC\NormBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('norm:test')
            ->setDescription('Run all unit tests')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        system("phpunit --verbose -c ../../../../../../app " . __DIR__ . "/../Tests");
    }
}
