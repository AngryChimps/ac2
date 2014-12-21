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
                InputArgument::OPTIONAL,
                'What is the name of the realm to generate?'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $realmName = $input->getArgument('realm');

        $generator = new Generator();
        if($realmName !== null) {
            $generator->generate($realmName, $this->getContainer()->get('kernel')->environment);
        }
        else{
            $generator->generateAll($this->getContainer()->get('kernel')->environment);
        }
    }
}
