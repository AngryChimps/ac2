<?php
namespace AC\NormBundle\core;

use \AC\NormBundle\config\Config;
use \AC\NormBundle\core\exceptions\OneValueQueryException;

class Norm {
    protected static $db_handles;


    public function initialize() {
        $config = Config::getInstance();

        foreach($config->databases as $db) {
            self::initialize_db($db);
        }
    }


    protected static function initialize_db($db) {
        $dbh = new \PDO(
            $db['database_type'] . ':' . 'host=' . $db['hostname'] . ';dbname=' . $db['database_name'],
            $db['username'],
            $db['password'],
            $db['persistent_connection'] ? array(\PDO::ATTR_PERSISTENT => true) : array(\PDO::ATTR_PERSISTENT => true)
            );

        $dbh->setAttribute(\PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        self::$db_handles[$db['shortname']] = $dbh;
    }

}