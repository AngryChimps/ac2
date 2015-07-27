<?php


namespace AC\NormBundle\services;


use AC\NormBundle\core\generator\types\Norm;
use Handlebars\Handlebars;
use Symfony\Component\Yaml\Dumper;
use Norm\riak\Address;

/*
 * This is what the generated Realm Information looks like
 *
 * $realms
 */
class InfoService {
    /** @var  string The environment */
    protected $environment;

    protected static $structure;

    public function __construct($environment, CreatorService $realmInfoCreatorService)
    {
        $this->environment = $environment;

        //Create the realm info files if they aren't already in the cache
        $realmInfoCreatorService->createIfNecessary();

        require(__DIR__ . '/../../../../../../app/cache/' . $this->environment . '/norm/structure.php');

        self::$structure = $structure;
    }

//    public function getTableNames() {
//        return self::$structure['tableNames'];
//    }
//
    public function getClassName($tableName) {
        if(isset(self::$structure['entities'][$tableName]['objectName'])) {
            return self::$structure['entities'][$tableName]['objectName'];
        }
        else {
            return self::$structure['subclasses'][$tableName]['objectName'];
        }
    }

    public function getDatastoreName($className) {
        $className = ltrim($className, '\\');
        return self::$structure['classes'][$className]['primaryDatastoreName'];
    }

    public function getDatastorePrefix($datastoreName) {
        return self::$structure['datastores'][$datastoreName]['prefix'];
    }

    public function getEntityName($className) {
        $className = ltrim($className, '\\');

        return self::$structure['classes'][$className]['name'];
    }

//    public function getPkData($obj) {
//        $class = get_class($obj);
//
//        $realm = $this->getRealm($class);
//        $tableName = $this->getTableName($class);
//
//        $pkProperties = self::$realmInfo[$realm][$tableName]['primaryKeyPropertyNames'];
//        $arr = array();
//        if($this->isCollection($obj)) {
//            foreach($obj as $object) {
//                $objArr = array();
//                foreach ($pkProperties as $prop) {
//                    $objArr[$prop] = $object->$prop;
//                }
//                $arr[] = $objArr;
//            }
//        }
//        else {
//            foreach ($pkProperties as $prop) {
//                $arr[$prop] = $obj->$prop;
//            }
//        }
//        return $arr;
//    }

//    public function getAutoIncrementPropertyName($obj) {
//        $class = get_class($obj);
//        $tableName = $this->getTableName($class);
//
//        return self::$structure['tables'][$tableName]['autoIncrementProperty'];
//    }
//
    public function getTableInfo($class) {
        $tableName = $this->getEntityName($class);

        return self::$structure['entities'][$tableName];
    }

    public function isCollection($obj) {
        $class = get_class($obj);
        return strpos($class, 'Collection') == strlen($class) - 10;
    }

    public function getAllApiSettableFields($entityName) {
        return array_merge($this->getApiPublicFields($entityName), $this->getApiPrivateFields($entityName),
                           $this->getApiHiddenButSettableFields($entityName));
    }

    public function getApiPublicFields($entityName) {
        return self::$structure['entities'][$entityName]['apiPublicFields'];
    }

    public function getApiPrivateFields($entityName) {
        return self::$structure['entities'][$entityName]['apiPrivateFields'];
    }

    public function getApiHiddenButSettableFields($entityName) {
        return self::$structure['entities'][$entityName]['apiHiddenButSettableFields'];
    }

    public function getFieldNames($entityName) {
        return self::$structure['entities'][$entityName]['fieldNames'];
    }
}