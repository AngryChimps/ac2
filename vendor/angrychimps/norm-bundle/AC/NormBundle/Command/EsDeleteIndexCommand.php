<?php


namespace AC\NormBundle\Command;

use AC\NormBundle\core\generator\Generator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AC\NormBundle\Services\RealmInfoService;
use AC\NormBundle\Services\DatastoreService;

class EsDeleteIndexCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('es:delete_index')
            ->setDescription('Delete an elasticsearch index')
            ->addArgument(
                'realmName',
                InputArgument::REQUIRED,
                'What is the realm named?'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $realmName = $input->getArgument('realmName');

        $realmService = $this->getContainer()->get('ac_norm.norm.' . $realmName);

        $realmService->deleteIndex();
    }
}
