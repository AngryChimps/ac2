<?php

namespace AngryChimps\AcBundle\Controller;

use Norm\riak\Session;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\ClassLoader\ClassMapGenerator;
use Symfony\Component\Debug\DebugClassLoader;

/**
 * Class TestController
 * @package AngryChimps\AcBundle\Controller
 * @Route("/test")
 */
class TestController extends Controller
{
//    /**
//     * @Route("/riakSave")
//     */
//    public function riakSaveAction() {
//        $obj = new \stdClass();
//        $obj->arr = [];
//        $obj->obj = new \stdClass();
//        $obj->a = "a";
//        $obj->one = 1;
//        $obj->stringArray = ['c', 'd'];
//        $obj->numArray = [1,2];
//
//        $json = json_encode($obj);
//
//        $client = new \Basho\Riak\Riak('127.0.0.1', 8098);
//        $bucket = new \Basho\Riak\Bucket($client, 'test');
//        $t1 = $bucket->newObject('t1');
//        $t1->setData($obj);
//        $t1->store();
//
//        return new Response('done');
//    }

    /**
     * @Route("/sessionSave")
     */
    public function sessionSaveAction() {
//        require(__DIR__ . '/../../../../vendor/angrychimps/norm-bundle/AC/NormBundle/core/datastore/AbstractDatastore.php');
////        require('/mnt/shared/ac/vendor/angrychimps/norm-bundle/AC/NormBundle/core/datastore/AbstractDatastore.php');
//        exit ('b');
        DebugClassLoader::enable();
        $sess = new Session();
        $sess->id = 'idgoeshere';
        $sess->browserHash = 'browserhash';
        $riak = $this->get('ac_norm.norm.riak');
        $riak->create($sess);
        return new Response('donesy');
    }

    /**
     * @Route("/sessionGet")
     */
    public function sessionGetAction() {
        $riak = $this->get('ac_norm.norm.riak');
        $sess = $riak->getSession('0d10fa032157b7c5403e99cf43e47434');
        print_r($sess);
        return new Response('done');
    }

    /**
     * @Route("/debugClasses")
     */
    public function debugClassesAction() {
        print_r(ClassMapGenerator::createMap(__DIR__.'/../../../../vendor/angrychimps/norm-bundle/AC'));
        ClassMapGenerator::dump(__DIR__.'/../../../../vendor/angrychimps/norm-bundle/AC', '/tmp/class_map.php');
    }
}
