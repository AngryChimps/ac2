<?php


namespace AC\NormBundle\Services;


use AC\NormBundle\core\generator\types\Table;
use AC\NormBundle\core\Utils;
use AC\NormBundle\core\generator\generators\YamlGenerator;
use AC\NormBundle\core\generator\types\Schema;
use AC\NormBundle\core\generator\types\Norm;
use Handlebars\Handlebars;
use AC\NormBundle\core\generator\types\Enum;
use Symfony\Component\Yaml\Dumper;
use Norm\riak\Address;

/*
 * This is what the generated Realm Information looks like
 *
 * $realms
 */
class RealmInfoService {
    /** @var  string The environment */
    protected $environment;

    protected static $realmInfo;
    protected static $classInfo;

    public function __construct($environment, RealmInfoCreatorService $realmInfoCreatorService)
    {
        $this->environment = $environment;

        //Create the realm info files if they aren't already in the cache
        $realmInfoCreatorService->createIfNecessary();

        require(__DIR__ . '/../../../../../../app/cache/' . $this->environment . '/angrychimps/norm/realmProperties.php');

        self::$realmInfo = $realms;
        self::$classInfo = $classes;
    }

    public function getDatastore($className) {
        if(strpos($className, "\\") === 0) {
            $className = substr($className, 1);
        }
        return self::$classInfo[$className]['primary_datastore'];
    }

    public function getDatastoreByRealm($realmName) {
        return self::$realmInfo[$realmName]['primaryDatastore'];
    }

    public function getRealm($className) {
        if(strpos($className, "\\") === 0) {
            $className = substr($className, 1);
        }

        return self::$classInfo[$className]['realmName'];
    }

    public function getTableName($className) {
        if(strpos($className, "\\") === 0) {
            $className = substr($className, 1);
        }

        return self::$classInfo[$className]['tableName'];
    }

    public function getTableNames($realmName) {
        return self::$realmInfo[$realmName]['tableNames'];
    }

    public function getClassName($realmName, $tableName) {
        return self::$realmInfo[$realmName][$tableName]['objectName'];
    }

    public function getPkData($obj) {
        $class = get_class($obj);

        $realm = $this->getRealm($class);
        $tableName = $this->getTableName($class);

        $pkProperties = self::$realmInfo[$realm][$tableName]['primaryKeyPropertyNames'];
        $arr = array();
        if($this->isCollection($obj)) {
            foreach($obj as $object) {
                $objArr = array();
                foreach ($pkProperties as $prop) {
                    $objArr[$prop] = $object->$prop;
                }
                $arr[] = $objArr;
            }
        }
        else {
            foreach ($pkProperties as $prop) {
                $arr[$prop] = $obj->$prop;
            }
        }
        return $arr;
    }

    public function getPkFieldNames($obj) {
        $class = get_class($obj);

        $realm = $this->getRealm($class);
        $tableName = $this->getTableName($class);

        $pkProperties = self::$realmInfo[$realm][$tableName]['primaryKeyPropertyNames'];
        $arr = array();
        if($this->isCollection($obj)) {
            foreach($obj as $object) {
                $objArr = array();
                foreach ($pkProperties as $prop) {
                    $objArr[$prop] = $object->$prop;
                }
                $arr[] = $objArr;
            }
        }
        else {
            foreach ($pkProperties as $prop) {
                $arr[$prop] = $obj->$prop;
            }
        }
        return $arr;
    }

    public function getAutoIncrementPropertyName($obj) {
        $class = get_class($obj);

        $realm = $this->getRealm($class);
        $tableName = $this->getTableName($class);

        return self::$realmInfo[$realm][$tableName]['autoIncrementProperty'];
    }

    public function getTableInfo($class) {
        $realm = $this->getRealm($class);
        $tableName = $this->getTableName($class);

        return self::$realmInfo[$realm][$tableName];
    }

    public function isCollection($obj) {
        $class = get_class($obj);
        return strpos($class, 'Collection') == strlen($class) - 10;
    }
}