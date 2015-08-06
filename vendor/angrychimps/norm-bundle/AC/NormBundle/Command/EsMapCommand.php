<?php


namespace AC\NormBundle\Command;

use AC\NormBundle\core\datastore\EsDocumentDatastore;
use AC\NormBundle\core\generator\Generator;
use AC\NormBundle\Services\CreatorService;
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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var DatastoreService $datastoreService */
        $datastoreService = $this->getContainer()->get('ac_norm.datastore');

        /** @var CreatorService $cs */
        $cs = $this->getContainer()->get('ac_norm.creator');
        $cs->createIfNecessary(true);
        $cs->generateSchema();
        $cs->generateData();
        $data = $cs->getData();

        //Set up entity level stuff
        foreach($data['entities'] as $entityData) {
            foreach($entityData['datastores'] as $datastore) {
                switch($datastore['method']) {
                    case 'riak2_map':
//                        /** @var Riak2MapDatastore $ds */
//                        $ds = $datastoreService->getDatastore($datastore['name']);
//
//                        //Create Solr Indexes
//                        $engine = new Handlebars(array(
//                            'loader' => new \Handlebars\Loader\FilesystemLoader(__DIR__.'/../core/generator/templates/', array('extension' => 'handlebars')),
//                        ));
//                        $rendered = $engine->render('RiakSolrSchema', array_merge($entityData, ['dsInfo' => $datastore]));
//                        echo "Creating Solr schema for datastore: " . $datastore['name'] . ", entity: " . $entityData['name'] . "\n";
//                        $ds->createSolrSchema($entityData['name'], $rendered);
//                        echo "Creating Solr Index for datastore: " . $datastore['name'] . ", entity: " . $entityData['name'] . "\n";
//                        $ds->createSolrIndex($entityData['name']);
//                        echo "Associating bucket to Solr Index for datastore: " . $datastore['name'] . ", entity: " . $entityData['name'] . "\n";
//                        $ds->associateBucketToSolrIndex($entityData['name']);
                        break;

                    case 'es_document':
                        /** @var EsDocumentDatastore $ds */
                        $ds = $datastoreService->getDatastore($datastore['name']);

                        //Define mapping
                        $props = [];
                        foreach($entityData['fields'] as $field) {
                            $props[$field['name']] = [
                                'type' => $field['elasticsearchType'],
                                'include_in_all' => $field['includeInAll'],
//                                'index_name' => $field['indexName'],
                            ];
                        }
                        echo "Defining Elasticsearch Mapping for datastore: " . $datastore['name'] . ", entity: " . $entityData['name'] . "\n";
                        $ds->defineMapping($entityData['name'], $props);
                        break;
                }
            }
        }
    }
}
