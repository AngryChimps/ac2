<?php


namespace AC\NormBundle\Command;

use AC\NormBundle\core\generator\Generator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AC\NormBundle\services\InfoService;
use AC\NormBundle\Services\DatastoreService;

class RiakCreateTypesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('riak:create_types')
            ->setDescription('Create necessary bucket types')
            ->addArgument(
                'datastoreName',
                InputArgument::OPTIONAL,
                'Which datastore should we create types for?'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $datastoreName = $input->getArgument('datastoreName');
        $this->createType($datastoreName);
     }

    protected function createType($datastoreName) {
        /** @var InfoService $infoService */
        $infoService = $this->getContainer()->get('ac_norm.info');
        $typeName = $infoService->getDatastorePrefix($datastoreName) . 'class_maps';

        system("riak-admin bucket-type create $typeName '{\"props\":{\"datatype\":\"map\"}}'");
        system("riak-admin bucket-type activate $typeName");
    }
}
