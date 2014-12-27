<?php

namespace AC\NormBundle\core\datastore;

use Psr\Log\LoggerInterface;

abstract class AbstractDatastore {
    abstract public function createObject($obj, &$debug);
    abstract public function createCollection($coll, &$debug);
    abstract public function updateObject($obj, &$debug);
    abstract public function updateCollection($coll, &$debug);
    abstract public function deleteObject($obj, &$debug);
    abstract public function deleteCollection($coll, &$debug);
    abstract public function populateObjectByPks($obj, $pks, &$debug);
    abstract public function populateCollectionByPks($obj, $pks, &$debug);

    protected function populateObjectWithArray($obj, $arr)
    {
        $this->loggerService->info(print_r($obj, true));
        $this->loggerService->info(print_r($arr, true));
        $class = get_class($obj);
        $tableInfo = $this->realmInfo->getTableInfo($class);

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
                            $tableInfo2 = $this->realmInfo->getTableInfo($fieldType);
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
        else {
            return false;
        }
        return strpos($class, 'Collection') == strlen($class) - 10;
    }
}