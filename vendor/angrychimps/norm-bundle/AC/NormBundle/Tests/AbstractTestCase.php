<?php


namespace AC\NormBundle\Tests;


use AC\NormBundle\core\datastore\DatastoreManager;
use AC\NormBundle\core\exceptions\ObjectAlreadyDeletedException;

class AbstractTestCase extends \PHPUnit_Framework_TestCase {
    protected static $_datastores;
    private static $objectsForCleanup;

    public function __construct() {
        if(self::$_datastores === null) {
            $contents = file_get_contents(__DIR__ . '/config/ac_norm_test.yml');
            $parsed = yaml_parse($contents);

            DatastoreManager::setDatastores($parsed['datastores']);

            self::$_datastores = $parsed['datastores'];
        }
    }

    protected function tearDown()
    {
//        foreach(self::$objectsForCleanup as $obj) {
//            try {
//                $obj->delete();
//            }
//            catch(\Exception $ex) {
//                //Do nothing
//            }
//        }
//        self::$objectsForCleanup = array();
//
        parent::tearDown();
    }

    protected static function addObjectForCleanup($obj) {
        self::$objectsForCleanup[] = $obj;
    }

}