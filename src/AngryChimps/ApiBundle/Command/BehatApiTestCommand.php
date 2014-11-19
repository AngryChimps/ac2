<?php


namespace AngryChimps\ApiBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BehatApiTestCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('behat:api')
            ->setDescription('Call behat functions in the api suite')
            ->addArgument(
                'behat_command',
                InputArgument::OPTIONAL,
                'What command would you like to execute?',
                'test'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = $input->getArgument('behat_command');
        switch($command) {
            case 'test':
                system("/usr/bin/php vendor/behat/behat/bin/behat --suite api");
                break;
            case 'init':
                system("/usr/bin/php vendor/behat/behat/bin/behat --init --suite api");
                break;
            default:
                throw new \Exception('Unknown command: ' . $command);
        }
    }
}
