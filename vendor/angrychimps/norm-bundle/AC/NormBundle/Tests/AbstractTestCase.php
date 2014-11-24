<?php


namespace AC\NormBundle\Tests;


use AC\NormBundle\core\datastore\DatastoreManager;

class AbstractTestCase extends \PHPUnit_Framework_TestCase {
    protected static $_datastores;

    public function __construct() {
        if(self::$_datastores === null) {
            $contents = file_get_contents(__DIR__ . '/config/ac_norm_test.yml');
            $parsed = yaml_parse($contents);

            DatastoreManager::setDatastores($parsed['datastores']);

            self::$_datastores = $parsed['datastores'];
        }
    }
}