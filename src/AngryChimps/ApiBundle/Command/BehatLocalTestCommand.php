<?php


namespace AngryChimps\ApiBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BehatLocalTestCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('behat:local')
            ->setDescription('Call behat functions in the api suite')
            ->addArgument(
                'behat_command',
                InputArgument::OPTIONAL,
                'What command would you like to execute?',
                'test'
            )
            ->addArgument(
                'behat_feature',
                InputArgument::OPTIONAL,
                'What feature would you like to test?'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = $input->getArgument('behat_command');
        $feature = $input->getArgument('behat_feature');

        switch($command) {
            case 'test':
                if($feature) {
                    system("/usr/bin/php vendor/behat/behat/bin/behat --suite api --name $feature");
                }
                else {
                    system("/usr/bin/php vendor/behat/behat/bin/behat --suite api");
                }
                break;
            case 'init':
                system("/usr/bin/php vendor/behat/behat/bin/behat --init --suite api");
                break;
            default:
                throw new \Exception('Unknown command: ' . $command);
        }
    }
}
