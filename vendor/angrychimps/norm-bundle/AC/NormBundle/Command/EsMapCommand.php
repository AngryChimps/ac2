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

class EsMapCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('es:map')
            ->setDescription('Upload mapping file for a given realm name')
            ->addArgument(
                'realmName',
                InputArgument::REQUIRED,
                'Which realm should we map?'
            )
            ->addArgument(
                'typeName',
                InputArgument::OPTIONAL,
                'Which type should we map (defaults to all)?'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $realmName = $input->getArgument('realmName');
        $typeName = $input->getArgument('typeName');

        /** @var RealmInfoService $realmInfo */
        $realmInfo = $this->getContainer()->get('ac_norm.realm_info');
        $realmService = $this->getContainer()->get('ac_norm.norm.' . $realmName);

        if(empty($typeName)) {;
            foreach($realmInfo->getTableNames($realmName) as $tableName) {
                $fullClassName = $realmInfo->getClassName($realmName, $tableName);
                $classParts = explode('\\', $fullClassName);
                $shortClassName = $classParts[count($classParts) - 1];
                $func = 'define' . $shortClassName . 'Mapping';
                $realmService->$func();
            }
        }
        else {
            $fullClassName = $realmInfo->getClassName($realmName, $typeName);
            $classParts = explode('\\', $fullClassName);
            $shortClassName = $classParts[count($classParts) - 1];
            $func = 'define' . $shortClassName . 'Mapping';
            $realmService->$func();
        }
    }
}
