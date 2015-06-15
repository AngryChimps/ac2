<?php

namespace AC\NormBundle\core\datastore;

use Psr\Log\LoggerInterface;
use AC\NormBundle\Services\RealmInfoService;
use AC\NormBundle\core\Utils;

abstract class AbstractDatastore {
    /** @var LoggerInterface  */
    protected static $loggerService;

    /** @var  RealmInfoService */
    protected static $realmInfo;

    public function __construct(RealmInfoService $realmInfo, LoggerInterface $loggerService) {
        self::$realmInfo = $realmInfo;
        self::$loggerService = $loggerService;
    }

    protected static function getAsArray($obj) {
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
                    $arr[] = self::getAsArray($object);
                }
                return $arr;

            case 'object':
                if (self::isCollection($obj)) {
                    $arr = [];
                    foreach ($obj as $key => $val) {
                        $arr[$key] = self::getAsArray($val);
                    }
                    return $arr;
                }
                elseif ($obj instanceof \DateTime) {
                    return $obj->format('c');
                }
                else {
                    $arr = [];
                    foreach ($obj as $key => $val) {
                        $arr[Utils::property2field($key)] = self::getAsArray($val);
                    }
                    return $arr;
                }

            default:
                throw new \Exception('Unknown object type: ' . gettype($obj));
        }
    }

    protected static function populateObjectWithArray($obj, $arr)
    {
        self::$loggerService->info(print_r($obj, true));
        self::$loggerService->info(print_r($arr, true));
        $class = get_class($obj);
        $tableInfo = self::$realmInfo->getTableInfo($class);

        if((is_array($arr) || self::isCollection($obj)) && empty($arr)) {
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
                    if (self::isCollection($fieldName)) {
                        $obj->$propertyName = new $fieldType();
                        foreach ($arr[$obj->$tableInfo['fieldNames'][$i]] as $objectArray) {
                            $tableInfo2 = self::$realmInfo->getTableInfo($fieldType);
                            $object = new $tableInfo2['objectName']();
                            self::populateObjectWithArray($object, $objectArray);
                            $obj->$tableInfo['propertyNames'][$i]->offsetSet(self::getIdentifier($object), $object);
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
                            self::populateObjectWithArray($obj2, $object);
                            $obj->{$propertyName}[] = $obj2;
                        }
//                        $object = new $className();
//                        if($arr[$fieldName] !== null) {
//                            self::populateObjectWithArray($object, $arr[$fieldName]);
//                        }
//                        $obj->$propertyName = $object;
                    }
                    elseif (class_exists($tableInfo['fieldTypes'][$i])) {
                        $object = new $tableInfo['fieldTypes'][$i]();
                        if($arr[$fieldName] !== null) {
                            self::populateObjectWithArray($object, $arr[$fieldName]);
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
    protected static function getIdentifier($obj) {
        $class = get_class($obj);
        $tableInfo = self::$realmInfo->getTableInfo($class);

        $pkArray = [];
        for($i = 0; $i < count($tableInfo['primaryKeyPropertyNames']); $i++) {
            $propertyName = $tableInfo['primaryKeyPropertyNames'][$i];
            $methodName = 'get' . ucfirst($propertyName);
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

    public static function isCollection($class) {
        if(is_object($class)) {
            $class = get_class($class);
        }
        else {
            return false;
        }
        return strpos($class, 'Collection') == strlen($class) - 10;
    }
}