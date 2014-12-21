<?php
/**
 * Created by PhpStorm.
 * User: sean
 * Date: 6/19/14
 * Time: 10:06 AM
 */

namespace AC\NormBundle\core\datastore;


use AC\NormBundle\core\NormBaseObject;
use AC\NormBundle\core\NormBaseCollection;
use AC\NormBundle\Services\RealmInfoService;
use Psr\Log\LoggerInterface;

abstract class AbstractDatastore {
    protected $connection;

    /** @var  RealmInfoService */
    protected $realmInfo;

    /** @var LoggerInterface  */
    protected $loggerService;

    abstract public function createObject($obj, &$debug);
    abstract public function createCollection($coll, &$debug);
    abstract public function updateObject($obj, &$debug);
    abstract public function updateCollection($coll, &$debug);
    abstract public function deleteObject($obj, &$debug);
    abstract public function deleteCollection($coll, &$debug);
    abstract public function populateObjectByPks($obj, $pks, &$debug);
    abstract public function populateCollectionByPks($obj, $pks, &$debug);

    public function __construct(RealmInfoService $realmInfo, LoggerInterface $loggerService) {
        $this->realmInfo = $realmInfo;
        $this->loggerService = $loggerService;
    }

    protected function populateObjectWithArray($obj, $arr)
    {
        $class = get_class($obj);
        $tableInfo = $this->realmInfo->getTableInfo($class);

        for($i = 0; $i < count($tableInfo['fieldNames']); $i++) {
            switch($tableInfo['fieldTypes'][$i]) {
                case 'int':
                    $obj->$tableInfo['propertyNames'][$i] = (int) array_values($arr)[$i];
                    break;
                case 'bool':
                    $obj->$tableInfo['propertyNames'][$i] = (bool) array_values($arr)[$i];
                    break;
                case 'float':
                case 'double':
                    $obj->$tableInfo['propertyNames'][$i] = (float) array_values($arr)[$i];
                    break;
                case 'Date':
                case 'DateTime':
                    $obj->$tableInfo['propertyNames'][$i] = new \DateTime(array_values($arr)[$i]);
                    break;
                case 'int[]':
                case 'float[]':
                case 'double[]':
                case 'string[]':
                    $obj->$tableInfo['propertyNames'][$i] = array_values($arr)[$i];
                    break;
                default:
                    if (class_exists($tableInfo['fieldTypes'][$i]) && $this->isCollection($tableInfo['fieldTypes'][$i])) {
                        $this->$propertyName = new $tableInfo['fieldTypes'][$i]();
                        foreach ($arr[$i] as $objectArray) {
                            $tableInfo2 = $this->realmInfo->getTableInfo($tableInfo['fieldTypes'][$i]);
                            $object = new $tableInfo2['objectClass']();
                            $this->populateObjectWithArray($object, $objectArray);
                            $this->$propertyName[$this->getIdentifier($object)] = $object;
                        }
                    } elseif (class_exists($tableInfo['fieldTypes'][$i])) {
                        $object = new $tableInfo['fieldTypes'][$i]();
                        $this->populateObjectWithArray($object, array_values($arr)[$i]);
                        $obj->$tableInfo['propertyNames'][$i] = $object;
                    } else {
                        $obj->$tableInfo['propertyNames'][$i] = array_values($arr)[$i];
                    }
            }
        }

    }

    protected function getIdentifier($obj) {
        $class = get_class($obj);
        $tableInfo = $this->realmInfo->getTableInfo($class);

        $pkArray = [];
        for($i = 0; $i < count($tableInfo['primaryKeyPropertyNames']); $i++) {
            if($tableInfo['fieldTypes'][$i] === 'DateTime') {
                $pkArray[] = $obj->$tableInfo['primaryKeyPropertyNames'][$i]->format('Y-m-d H:i:s');
            }
            elseif($tableInfo['fieldTypes'][$i] === 'Date') {
                $pkArray[] = $obj->$tableInfo['primaryKeyPropertyNames'][$i]->format('Y-m-d');
            }
            else {
                $pkArray[] = $obj->$tableInfo['primaryKeyPropertyNames'][$i];
            }
        }

        return implode('|', $pkArray);
    }

    public function isCollection($class) {
        if(is_object($class)) {
            $class = get_class($class);
        }
        return strpos($class, 'Collection') == strlen($class) - 10;
    }
}