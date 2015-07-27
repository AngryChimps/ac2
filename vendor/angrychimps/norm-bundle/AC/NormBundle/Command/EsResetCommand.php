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

class EsResetCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('es:reset')
            ->setDescription('Reset an elasticsearch index')
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

        $realmService->deleteIndex();
        $realmService->createIndex();

        //Map the index
        foreach($realmInfo->getTableNames($realmName) as $tableName) {
            $fullClassName = $realmInfo->getClassName($realmName, $tableName);
            $classParts = explode('\\', $fullClassName);
            $shortClassName = $classParts[count($classParts) - 1];
            $func = 'define' . $shortClassName . 'Mapping';
            $realmService->$func();
        }
    }
}
