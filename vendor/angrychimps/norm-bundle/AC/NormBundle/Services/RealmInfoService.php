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
    /** @var string[] All of the realm names */
    private $realmNames = [];

    private $realms = [];

    /** @var  string The environment */
    protected $environment;

    /** @var Norm An array of schemas */
    protected $norm;

    /** @var array The data associated with all of the realms */
    protected $data = [];

    protected $realmInfo;
    protected $classInfo;

    public function __construct($environment, RealmInfoCreatorService $realmInfoCreatorService)
    {
        $this->environment = $environment;

        //Create the realm info files if they aren't already in the cache
        $realmInfoCreatorService->createIfNecessary();

        require(__DIR__ . '/../../../../../../app/cache/' . $this->environment . '/angrychimps/norm/realmProperties.php');

        $this->realmInfo = $realms;
        $this->classInfo = $classes;
    }

    public function getDatastore($className) {
        return $this->classInfo[$className]['primary_datastore'];
    }

    public function getRealm($className) {
        return $this->classInfo[$className]['realmName'];
    }

    public function getTableName($className) {
        return $this->classInfo[$className]['tableName'];
    }

    public function getPkData($obj) {
        $class = get_class($obj);

        $realm = $this->getRealm($class);
        $tableName = $this->getTableName($class);

        $pkProperties = $this->realmInfo[$realm][$tableName]['primaryKeyPropertyNames'];
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

        $pkProperties = $this->realmInfo[$realm][$tableName]['primaryKeyPropertyNames'];
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

        return $this->realmInfo[$realm][$tableName]['autoIncrementProperty'];
    }

    public function getTableInfo($class) {
        $realm = $this->getRealm($class);
        $tableName = $this->getTableName($class);

        return $this->realmInfo[$realm][$tableName];
    }

    public function isCollection($obj) {
        $class = get_class($obj);
        return strpos($class, 'Collection') == strlen($class) - 10;
    }
}