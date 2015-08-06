<?php

namespace AC\NormBundle\core\datastore;

use Psr\Log\LoggerInterface;
use AC\NormBundle\services\InfoService;
use AC\NormBundle\core\Utils;

abstract class AbstractDatastore {
    /** @var LoggerInterface  */
    protected $loggerService;

    /** @var  InfoService */
    protected $infoService;

    public abstract function createObject($obj, &$debug);
    public abstract function createCollection($coll, &$debug);
    public abstract function updateObject($obj, &$debug);
    public abstract function updateCollection($coll, &$debug);
    public abstract function deleteObject($obj, &$debug);
    public abstract function deleteCollection($coll, &$debug);
    public abstract function populateObjectByPks($obj, $pks, &$debug);
    public abstract function populateObjectByQuery($obj, $query, $limit, $offset, &$debug);
    public abstract function populateCollectionByPks($coll, $pks, &$debug);
    public abstract function populateCollectionByQuery(\ArrayObject $coll, $query, $limit, $offset, &$debug);


    public function __construct(InfoService $infoService, LoggerInterface $loggerService) {
        $this->infoService = $infoService;
        $this->loggerService = $loggerService;
    }

    protected function getAsArray($obj) {
        switch (gettype($obj)) {
            case 'NULL':
                return null;

            case 'boolean':
            case 'integer':
            case 'double':
            case 'string':
                return $obj;

            case 'array':
                $arr = [];
                foreach ($obj as $object) {
                    $arr[] = $this->getAsArray($object);
                }
                return $arr;

            case 'object':
                if ($this->isCollection($obj)) {
                    $arr = [];
                    foreach ($obj as $key => $val) {
                        $arr[$key] = $this->getAsArray($val);
                    }
                    return $arr;
                }
                elseif ($obj instanceof \DateTime) {
                    return $obj->format('c');
                }
                else {
                    $arr = [];
                    foreach ($obj as $key => $val) {
                        $arr[Utils::property2field($key)] = $this->getAsArray($val);
                    }
                    return $arr;
                }

            default:
                throw new \Exception('Unknown object type: ' . gettype($obj));
        }
    }

    protected function populateObjectWithArray($obj, $arr)
    {
        $this->loggerService->info(print_r($obj, true));
        $this->loggerService->info(print_r($arr, true));
        $class = get_class($obj);
        $tableInfo = $this->infoService->getTableInfo($class);

        if((is_array($arr) || $this->isCollection($obj)) && empty($arr)) {
            return [];
        }
        for($i = 0; $i < count($tableInfo['fieldNames']); $i++) {
            $fieldName = $tableInfo['fieldNames'][$i];
            $fieldType = $tableInfo['fieldTypes'][$i];
            $propertyName = $tableInfo['propertyNames'][$i];

            switch($tableInfo['fieldTypes'][$i]) {
                case 'string':
                    $obj->$propertyName = $arr[$fieldName];
                    break;
                case 'int':
                    $obj->$propertyName = (int) $arr[$fieldName];
                    break;
                case 'bool':
                    $obj->$propertyName = (bool) $arr[$fieldName];
                    break;
                case 'float':
                case 'double':
                    $obj->$propertyName = (float) $arr[$fieldName];
                    break;
                case 'Date':
                case 'DateTime':
                    $obj->$propertyName = new \DateTime($arr[$fieldName]);
                    break;
                case 'int[]':
                case 'float[]':
                case 'double[]':
                case 'string[]':
                    $obj->$propertyName = array_values($arr[$fieldName]);
                    break;
                case 'Date[]':
                case 'DateTime[]':
                    $obj->$propertyName = [];
                    foreach($arr[$obj->$fieldName] as $val) {
                        $obj->{$tableInfo['propertyNames'][$i]}[] = new \DateTime($val);
                    }
                    break;
                default:
                    //Norm collection
                    if ($this->isCollection($fieldName)) {
                        $obj->$propertyName = new $fieldType();
                        foreach ($arr[$obj->$tableInfo['fieldNames'][$i]] as $objectArray) {
                            $tableInfo2 = $this->infoService->getTableInfo($fieldType);
                            $object = new $tableInfo2['objectName']();
                            $this->populateObjectWithArray($object, $objectArray);
                            $obj->$tableInfo['propertyNames'][$i]->offsetSet($this->getIdentifier($object), $object);
                        }
                    }
                    //Empty array of Norm objects
                    elseif(empty($arr) && (strpos($fieldType, '[]') === strlen($fieldType) - 2)
                        && class_exists(substr($fieldType, 0, strlen($fieldType) - 2))) {
                        $obj->$propertyName = [];
                    }
                    //Array of Norm objects
                    elseif ((strpos($fieldType, '[]') === strlen($fieldType) - 2)
                            && class_exists(substr($fieldType, 0, strlen($fieldType) - 2))) {
                        $className = substr($fieldType, 0, strlen($fieldType) - 2);
                        $obj->$propertyName = [];
                        foreach($arr[$fieldName] as $object) {
                            $obj2 = new $className();
                            $this->populateObjectWithArray($obj2, $object);
                            $obj->{$propertyName}[] = $obj2;
                        }
//                        $object = new $className();
//                        if($arr[$fieldName] !== null) {
//                            $this->populateObjectWithArray($object, $arr[$fieldName]);
//                        }
//                        $obj->$propertyName = $object;
                    }
                    elseif (class_exists($tableInfo['fieldTypes'][$i])) {
                        $object = new $tableInfo['fieldTypes'][$i]();
                        if($arr[$fieldName] !== null) {
                            $this->populateObjectWithArray($object, $arr[$fieldName]);
                        }
                        $obj->$propertyName = $object;
                    }
                    else {
                        $obj->$propertyName = $arr[$fieldName];
                    }
            }
        }

    }

    /**
     * @param $obj
     * @return string
     */
    protected function getIdentifier($obj) {
        $class = get_class($obj);
        $tableInfo = $this->infoService->getTableInfo($class);

        $pkArray = [];
        for($i = 0; $i < count($tableInfo['primaryKeyFieldNames']); $i++) {
            $fieldName = $tableInfo['primaryKeyFieldNames'][$i];
            $methodName = 'get' . ucfirst(Utils::field2property($fieldName));
            if($tableInfo['fieldTypes'][$i] === 'DateTime') {
                $pkArray[] = $obj->$methodName()->format('c');
            }
            elseif($tableInfo['fieldTypes'][$i] === 'Date') {
                $pkArray[] = $obj->$methodName()->format('c');
            }
            else {
                $pkArray[] = $obj->$methodName();
            }
        }

        return implode('|', $pkArray);
    }

    public function isCollection($class) {
        if(is_object($class)) {
            $class = get_class($class);
        }
        else {
            return false;
        }
        return strpos($class, 'Collection') == strlen($class) - 10;
    }
}