<?php


namespace AC\NormBundle\Command;

use AC\NormBundle\core\datastore\AbstractRiak2Datastore;
use AC\NormBundle\core\datastore\Riak2MapDatastore;
use AC\NormBundle\core\generator\Generator;
use AC\NormBundle\Services\CreatorService;
use AngryChimps\GuzzleBundle\Services\GuzzleService;
use Handlebars\Handlebars;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AC\NormBundle\services\InfoService;
use AC\NormBundle\Services\DatastoreService;

class RiakCreateSolrIndexesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('riak:create_solr_indexes')
            ->setDescription('Create necessary SOLR indexes')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var InfoService $infoService */
        $infoService = $this->getContainer()->get('ac_norm.info');

        /** @var DatastoreService $datastoreService */
        $datastoreService = $this->getContainer()->get('ac_norm.datastore');

        /** @var CreatorService $cs */
        $cs = $this->getContainer()->get('ac_norm.creator');
        $cs->generateData();
        $data = $cs->getData();

        $engine = new Handlebars(array(
            'loader' => new \Handlebars\Loader\FilesystemLoader(__DIR__.'/../core/generator/templates/', array('extension' => 'handlebars')),
        ));

        foreach($data['entities'] as $entityData) {
            $rendered = $engine->render('RiakSolrSchema', $entityData);
            $dsName = $infoService->getPrimaryDatastoreName($entityData['datastores'][0]['name']);

            /** @var Riak2MapDatastore $ds */
            $ds = $datastoreService->getDatastore($dsName);
            $ds->createSolrSchema($entityData['name'], $rendered);
//            $ds->createSolrIndex($entityData['name']);
//            $ds->associateBucketToSolrIndex($entityData['name']);
        }
     }
}
