<?php


namespace AC\NormBundle\core\datastore;


use AC\NormBundle\config\Config;

class MysqlPdoDatastore extends AbstractPdoDatastore{
    public function getClassConfigs($realm)
    {
        $configs = array();
        $referenceDatastore = Config::$realms[$realm]['referenceDatastore'];
        $schemaName = Config::$datastores[$referenceDatastore]['dbname'];

        $sql = 'SELECT TABLE_NAME, TABLE_COMMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = :schemaName';
        $params = array(':schemaName' => $schemaName);

        $stmt = $this->query($sql, $params);

        while($row = $stmt->fetch()) {
            $commentArray = array();
            $tableName = $row['TABLE_NAME'];
            $comments = isset($$row['TABLE_COMMENT']) ? $row['TABLE_COMMENT'] : null;

            if($comments !== null) {
                $lines = explode("\n", $comments);
                foreach($lines as $line) {
                    $parts = explode('=', $line, 1);
                    $lpart = trim($parts[0]);
                    $rpart = trim($parts[1]);
                    $commentArray[] = array($lpart => $rpart);
                }
                $configs[$tableName] = $commentArray;
            }
        }

        return $configs;
    }

} 