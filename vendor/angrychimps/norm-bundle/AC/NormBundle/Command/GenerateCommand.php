<?php


namespace AC\NormBundle\Command;

use AC\NormBundle\core\generator\Generator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AC\NormBundle\Services\CreatorService;

class GenerateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('norm:generate')
            ->setDescription('Generate objects for a specific realm')
            ->addArgument(
                'environ',
                InputArgument::OPTIONAL,
                'Which environment should we generate?'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $env = $input->getArgument('environ');

        /** @var CreatorService $cs */
        $cs = $this->getContainer()->get('ac_norm.creator');
        if($env !== null) {
            $cs->setEnvironment($env);
        }

        $cs->createIfNecessary(true);
    }
}
