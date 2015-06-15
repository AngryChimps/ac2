<?php


namespace AC\NormBundle\Tests;

require_once __DIR__ . '/../vendor/phpunit/phpunit/src/Framework/Assert/Functions.php';

abstract class AbstractRiakTestCase extends AbstractTestCase {
    const PREFIX = '__norm';
    const REALM = 'riak';

    /** @var \Riak\Connection  */
    private static $conn = null;

    /*
     * Returns the test database connection.
     *
     * @return \Riak\Connection
     */
    final public function getConnection()
    {
        if(self::$conn === null) {
            $realms_contents = file_get_contents(__DIR__ . "/config/ac_norm.yml");
            $realms_parsed = yaml_parse($realms_contents);
            $datastores_contents = file_get_contents(__DIR__ . "/config/ac_norm_test.yml");
            $datastores_parsed = yaml_parse($datastores_contents);

            $datastoreName = $realms_parsed['realms'][self::REALM]['primary_datastore'];
            $datastoreInfo = $datastores_parsed['datastores'][$datastoreName];

            self::$conn = new \Riak\Connection($datastoreInfo['host'], $datastoreInfo['port']);
        }

        return self::$conn;
    }

    protected function getKeyName($primaryKeys) {
        foreach($primaryKeys as &$primaryKey) {
            if($primaryKey instanceof \DateTime) {
                $primaryKey = $primaryKey->format('Y-m-d H:i:s');
            }
        }
        return implode('|', $primaryKeys);
    }

    final public function getObjectsBucket($tablename) {
        $bucketName = self::PREFIX . ':' . self::REALM . ':' . $tablename . ':objects';
        return new \Riak\Bucket($this->getConnection(), $bucketName);
    }

}