<?php


namespace AC\NormBundle\Command;

use AC\NormBundle\core\datastore\EsDocumentDatastore;
use AC\NormBundle\core\datastore\Riak2MapDatastore;
use AC\NormBundle\Services\CreatorService;
use AC\NormBundle\Services\DatastoreService;
use AC\NormBundle\Services\InfoService;
use Handlebars\Handlebars;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NormSetupCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('norm:setup')
            ->setDescription('Setup all datastores')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $datastores = $this->getContainer()->getParameter('ac_norm.datastores');

        /** @var InfoService $infoService */
        $infoService = $this->getContainer()->get('ac_norm.info');

        /** @var DatastoreService $datastoreService */
        $datastoreService = $this->getContainer()->get('ac_norm.datastore');

        /** @var CreatorService $cs */
        $cs = $this->getContainer()->get('ac_norm.creator');
        $cs->createIfNecessary(true);
        $cs->generateSchema();
        $cs->generateData();
        $data = $cs->getData();

        //Setup datastore level stuff
        foreach($datastores as $datastoreName => $datastore) {
            switch($datastore['driver']) {
                case 'riak2':
                    //Create types (may fail if created, but no harm done)
                    echo "Creating riak types for $datastoreName\n";
                    $typeName = $infoService->getDatastorePrefix($datastoreName) . 'class_maps';
                    system("riak-admin bucket-type create $typeName '{\"props\":{\"datatype\":\"map\"}}'");
                    system("riak-admin bucket-type activate $typeName");
                    break;

                case 'elasticsearch':
                    echo "Creating elasticsearch index for $datastoreName\n";
                    /** @var EsDocumentDatastore $ds */
                    $ds = $datastoreService->getDatastore($datastoreName);
                    $ds->createIndex($datastore['shards'], $datastore['replicas']);
                    break;
            }
        }

        //Set up entity level stuff
        foreach($data['entities'] as $entityData) {
            foreach($entityData['datastores'] as $datastore) {
                switch($datastore['method']) {
                    case 'riak2_map':
                        /** @var Riak2MapDatastore $ds */
                        $ds = $datastoreService->getDatastore($datastore['name']);

                        //Create Solr Indexes
                        $engine = new Handlebars(array(
                            'loader' => new \Handlebars\Loader\FilesystemLoader(__DIR__.'/../core/generator/templates/', array('extension' => 'handlebars')),
                        ));
                        $rendered = $engine->render('RiakSolrSchema', array_merge($entityData, ['dsInfo' => $datastore]));
                        echo "Creating Solr schema for datastore: " . $datastore['name'] . ", entity: " . $entityData['name'] . "\n";
                        $ds->createSolrSchema($entityData['name'], $rendered);
                        echo "Creating Solr Index for datastore: " . $datastore['name'] . ", entity: " . $entityData['name'] . "\n";
                        $ds->createSolrIndex($entityData['name']);
                        echo "Associating bucket to Solr Index for datastore: " . $datastore['name'] . ", entity: " . $entityData['name'] . "\n";
                        $ds->associateBucketToSolrIndex($entityData['name']);
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
