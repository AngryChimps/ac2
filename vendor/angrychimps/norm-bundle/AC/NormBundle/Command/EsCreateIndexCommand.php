<?php


namespace AC\NormBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EsCreateIndexCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('es:create_index')
            ->setDescription('Create an elasticsearch index')
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

        /** @var RealmInfoService $realmInfo */
        $realmInfo = $this->getContainer()->get('ac_norm.realm_info');

        $realmService->createIndex();
    }
}
