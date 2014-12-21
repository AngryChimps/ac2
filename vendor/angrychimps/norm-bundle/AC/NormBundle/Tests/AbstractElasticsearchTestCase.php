<?php


namespace AC\NormBundle\Tests;

use Elastica\Client;

require_once __DIR__ . '/../vendor/phpunit/phpunit/src/Framework/Assert/Functions.php';

abstract class AbstractElasticsearchTestCase extends AbstractTestCase {
    const REALM = 'es';

    /** @var  \Elastica\Client */
    private static $client;

    /** @var  \Elastica\Index */
    private static $index;

    /*
     * Returns the elasticsearch index
     *
     * @return \Elastica\Index
     */
    final public function getIndex()
    {
        if(self::$client === null) {
            $realms_contents = file_get_contents(__DIR__ . "/config/ac_norm.yml");
            $realms_parsed = yaml_parse($realms_contents);
            $datastores_contents = file_get_contents(__DIR__ . "/config/ac_norm_test.yml");
            $datastores_parsed = yaml_parse($datastores_contents);

            $datastoreName = $realms_parsed['realms'][self::REALM]['primary_datastore'];
            $datastoreInfo = $datastores_parsed['datastores'][$datastoreName];

            self::$client = new Client(array('servers'=>$datastoreInfo['servers']));
            self::$index = self::$client->getIndex($datastoreInfo['index_name']);
        }

        return self::$index;
    }

    protected function getKeyName($primaryKeys) {
        foreach($primaryKeys as &$primaryKey) {
            if($primaryKey instanceof \DateTime) {
                $primaryKey = $primaryKey->format('Y-m-d H:i:s');
            }
        }
        return implode('|', $primaryKeys);
    }
}