<?php

namespace AngryChimps\ApiBundle\Controller;

use AC\NormBundle\core\datastore\AbstractElasticsearchDatastore;
use AC\NormBundle\core\datastore\EsDocumentDatastore;
use AC\NormBundle\core\datastore\Riak2MapDatastore;
use AC\NormBundle\Services\DatastoreService;
use AC\NormBundle\Services\InfoService;
use FOS\RestBundle\Controller\FOSRestController;
use Handlebars\Handlebars;
use Norm\Member;
use Riak\Client\Command\Bucket\StoreBucketProperties;
use Riak\Client\Command\DataType\FetchMap;
use Riak\Client\Command\DataType\StoreMap;
use Riak\Client\Command\Kv\StoreValue;
use Riak\Client\Core\Query\RiakLocation;
use Riak\Client\Core\Query\RiakNamespace;
use Riak\Client\Core\Query\RiakObject;
use Riak\Client\ProtoBuf\MapUpdate;
use Riak\Client\RiakClientBuilder;
use Symfony\Component\HttpFoundation\RequestStack;
use FOS\RestBundle\View\ViewHandler;
use AngryChimps\ApiBundle\Services\SessionService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use AngryChimps\ApiBundle\Services\ResponseService;
use Symfony\Component\HttpKernel\Kernel;
use AngryChimps\NormBundle\services\NormService;
use AC\NormBundle\Services\CreatorService;

class TestController extends AbstractController
{
    protected $debug;

    /** @var Kernel */
    protected $kernel;

    /** @var \Symfony\Component\DependencyInjection\ContainerInterface  */
    protected $container;

    /** @var NormService */
    protected $norm;

    public function __construct(RequestStack $requestStack, SessionService $sessionService,
                                ResponseService $responseService, $debug, Kernel $kernel, NormService $norm) {
        parent::__construct($requestStack, $sessionService, $responseService);
        $this->debug = $debug;
        $this->kernel = $kernel;
        $this->container = $kernel->getContainer();
        $this->norm = $norm;
    }

    public function runAction($methodName) {
        return $this->{$methodName}();
    }

    public function envAction() {
        $data = [
            'debug' => $this->debug,
            'env' => $this->kernel->getEnvironment(),
            'web_profiler.debug_toolbar.mode' => $this->container->getParameter('web_profiler.debug_toolbar.mode'),
        ];
        return $this->responseService->success($data);
    }

    public function createAction() {
        $member = new Member();
        $member->setFirst('Bob');
        $this->norm->create($member);
        return $this->responseService->success(['id' => $member->getId()]);
    }

    public function retrieveAction($id){
        $member = $this->riak->getMember($id);
        return $this->responseService->success(['fname' => $member->getFname()]);
    }

    protected function normGenerate() {
        /** @var CreatorService $cs */
        $cs = $this->kernel->getContainer()->get('ac_norm.creator');
        $cs->createIfNecessary(true);
        return $this->responseService->success($cs->getData());
    }

    protected function riakCreateType() {
        $datastoreName = 'riak_ds';

        /** @var InfoService $infoService */
        $infoService = $this->kernel->getContainer()->get('ac_norm.info');
        $typeName = $infoService->getDatastorePrefix($datastoreName) . 'class_maps';

        system("riak-admin bucket-type create $typeName '{\"props\":{\"datatype\":\"map\"}}'");
        system("riak-admin bucket-type activate $typeName");
    }

    protected function riakCreate() {
        $typeName = 'maptest';
        $key = 'a';
//        system("riak-admin bucket-type create maptest '{\"props\":{\"datatype\":\"map\"}}'");
//        system("riak-admin bucket-type activate maptest");

        $builder = new RiakClientBuilder();
        $client  = $builder
            ->withNodeUri('proto://127.0.0.1:8087')
            ->build();

//        $object    = new RiakObject();
        $namespace = new RiakNamespace($typeName, 'bucket_name');
        $location  = new RiakLocation($namespace, $key);

//        $object->setValue('[1,1,1]');
//        $object->setContentType('application/json');

//        $update = MapUpdate::createFromArray([
//            'counter_key'   => 10,
//            'flag_key'      => true,
//            'set_key'       => [1,2,3],
//            'register_key'  => "Register Val",
//            'map_key'       => [
//                'sub_counter_key'   => 10,
//                'sub_flag_key'      => true,
//                'sub_set_key'       => [1,2,3],
//                'sub_register_key'  => "Register Val",
//            ],
//        ]);


        $store = StoreMap::builder()
            ->withReturnBody(true)
            ->withPw(2)
            ->withDw(2)
            ->withW(3)
            ->updateRegister('url', 'google.com')
            ->updateCounter('clicks', 100)
            ->updateFlag('active', true)
            ->withLocation($location)
            ->build();

// store object
//        $store    = StoreValue::builder($location, $object)
//            ->withPw(1)
//            ->withW(2)
//            ->build();

        $client->execute($store);

        return $this->responseService->success(['status'=>'done']);
    }

    protected function riakGet() {
        $typeName = '__norm_local_class_maps';
        $bucketName = 'session';
        $key = '%242y%2410%24xhDVUOlfV1W6LjMGChRNw.VvFsCC.wsrULaN5AnzVkwqBOFsrPwKW';

        $builder = new RiakClientBuilder();
        $client  = $builder
            ->withNodeUri('proto://127.0.0.1:8087')
            ->build();
        $namespace = new RiakNamespace($typeName, $bucketName);
        $location  = new RiakLocation($namespace, $key);

        $fetch = FetchMap::builder()
            ->withNotFoundOk(true)
            ->withPr(1)
            ->withR(1)
            ->withLocation($location)
            ->build();

        $fetchResponse = $client->execute($fetch);
        $url = $fetchResponse->getDatatype()->get('id');
        return $this->responseService->success(['url'=>$url]);
    }

    protected function riakCreateIndexes() {
        /** @var InfoService $infoService */
        $infoService = $this->container->get('ac_norm.info');

        /** @var DatastoreService $datastoreService */
        $datastoreService = $this->container->get('ac_norm.datastore');

        /** @var CreatorService $cs */
        $cs = $this->container->get('ac_norm.creator');
        $cs->generateSchema();
        $cs->generateData();
        $data = $cs->getData();

        $engine = new Handlebars(array(
            'loader' => new \Handlebars\Loader\FilesystemLoader(__DIR__.'/../../../../vendor/angrychimps/norm-bundle/AC/NormBundle/core/generator/templates/', array('extension' => 'handlebars')),
        ));

        foreach($data['entities'] as $entityData) {
            foreach($entityData['datastores'] as $dsInfo) {
                $rendered = $engine->render('RiakSolrSchema', array_merge($entityData, ['dsInfo' => $dsInfo]));
//                 print_r($rendered); exit;

                /** @var Riak2MapDatastore $ds */
                $ds = $datastoreService->getDatastore($dsInfo['name']);
                $ds->createSolrSchema($entityData['name'], $rendered);
                $ds->createSolrIndex($entityData['name']);
                $ds->associateBucketToSolrIndex($entityData['name']);
            }
        }

        return $this->responseService->success([]);
    }

    protected function normSetup() {
        $datastores = $this->container->getParameter('ac_norm.datastores');

        /** @var InfoService $infoService */
        $infoService = $this->container->get('ac_norm.info');

        /** @var DatastoreService $datastoreService */
        $datastoreService = $this->container->get('ac_norm.datastore');

        /** @var CreatorService $cs */
        $cs = $this->container->get('ac_norm.creator');
        $cs->generateSchema();
        $cs->generateData();
        $data = $cs->getData();

        //Setup datastore level stuff
        foreach($datastores as $datastoreName => $datastore) {
            switch($datastore['driver']) {
                case 'riak2':
                    //Create types (may fail if created, but no harm done)
                    $typeName = $infoService->getDatastorePrefix($datastoreName) . 'class_maps';
                    system("riak-admin bucket-type create $typeName '{\"props\":{\"datatype\":\"map\"}}'");
                    system("riak-admin bucket-type activate $typeName");
                    break;

                case 'elasticsearch':
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
                            'loader' => new \Handlebars\Loader\FilesystemLoader(__DIR__.'/../../../../vendor/angrychimps/norm-bundle/AC/NormBundle/core/generator/templates/', array('extension' => 'handlebars')),
                        ));
                        $rendered = $engine->render('RiakSolrSchema', array_merge($entityData, ['dsInfo' => $datastore]));
                        $ds->createSolrSchema($entityData['name'], $rendered);
                        $ds->createSolrIndex($entityData['name']);
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
//                                'index_name' => $field['indexName'],
                            ];
                        }
                        $ds->defineMapping($entityData['name'], $props);
                        break;
                }
            }
        }

        return $this->responseService->success();
    }
}
