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
    public abstract function getQueryResultsCount($className, $query, &$debug);
    public abstract function search($entityName, $query, $limit, $offset, &$debug);


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
                    if($this->infoService->isSubclass(get_class($obj))) {
                        return $obj->getAsArray();
                    }
                    else {
                        $arr = [];
                        foreach ($obj->getMapValues() as $key => $val) {
                            $arr[Utils::property2field($key)] = $this->getAsArray($val);
                        }
                        return $arr;
                    }
                }

            default:
                throw new \Exception('Unknown object type: ' . gettype($obj));
        }
    }

    protected function getMapValues($obj, $arr, $isPropertyIndexed = false)
    {
        if((is_array($arr) || $this->isCollection($obj)) && empty($arr)) {
            return [];
        }

        if($isPropertyIndexed) {
            $fieldIndexedArray = [];
            foreach($arr as $key => $val) {
                $fieldIndexedArray[Utils::property2field($key)] = $val;
            }
        }
        else {
            $fieldIndexedArray = $arr;
        }


        $mapValues = [];
        $class = get_class($obj);
        $tableInfo = $this->infoService->getTableInfo($class);

        for($i = 0; $i < count($tableInfo['fieldNames']); $i++) {
            $fieldName = $tableInfo['fieldNames'][$i];
            $propertyName = $tableInfo['propertyNames'][$i];
            $fieldType = $tableInfo['fieldTypes'][$i];

            if(!isset($fieldIndexedArray[$fieldName])){
                continue;
            }

            switch($tableInfo['fieldTypes'][$i]) {
                case 'string':
                case 'Uuid':
                case 'Email':
                case 'Time':
                case 'text':
                    $mapValues[$propertyName] = $fieldIndexedArray[$fieldName];
                    break;

                case 'int':
                case 'Counter':
                case 'enum':
                    $mapValues[$propertyName] = (int) $fieldIndexedArray[$fieldName];
                    break;

                case 'bool':
                    $mapValues[$propertyName] = (bool) $fieldIndexedArray[$fieldName];
                    break;

                case 'float':
                case 'double':
                case 'Currency':
                case 'decimal':
                    $mapValues[$propertyName] = (float) $fieldIndexedArray[$fieldName];
                    break;

                case 'Date':
                case 'DateTime':
                    $mapValues[$propertyName] = new \DateTime($fieldIndexedArray[$fieldName]);
                    break;

                case 'int[]':
                case 'Counter[]':
                case 'enum[]':
                    if(!is_array($fieldIndexedArray[$fieldName])) {
                        $fieldIndexedArray[$fieldName] = [$fieldIndexedArray[$fieldName]];
                    }
                    $mapValues[$propertyName] = [];
                    foreach($fieldIndexedArray[$fieldName] as $val) {
                        $mapValues[$propertyName][] = (int) $val;
                    }
                    break;

                case 'float[]':
                case 'double[]':
                case 'set':
                    if(!is_array($fieldIndexedArray[$fieldName])) {
                        $fieldIndexedArray[$fieldName] = [$fieldIndexedArray[$fieldName]];
                    }
                    $mapValues[$propertyName] = [];
                    foreach($fieldIndexedArray[$fieldName] as $val) {
                        $mapValues[$propertyName][] = (float) $val;
                    }
                    break;

                case 'string[]':
                case 'Time[]':
                    if(!is_array($fieldIndexedArray[$fieldName])) {
                        $fieldIndexedArray[$fieldName] = [$fieldIndexedArray[$fieldName]];
                    }
                    $mapValues[$propertyName] = array_values($fieldIndexedArray[$fieldName]);
                    break;

                case 'Date[]':
                case 'DateTime[]':
                    if(!is_array($fieldIndexedArray[$fieldName])) {
                        $fieldIndexedArray[$fieldName] = [$fieldIndexedArray[$fieldName]];
                    }
                    foreach($fieldIndexedArray[$obj->$fieldName] as $val) {
                        $mapValues[$propertyName][] = new \DateTime($val);
                    }
                    break;

                case 'Location':
                    if(is_array($fieldIndexedArray[$fieldName])) {
                        $mapValues[$propertyName] = implode(', ', $fieldIndexedArray[$fieldName]);
                    }
                    else {
                        $mapValues[$propertyName] = $fieldIndexedArray[$fieldName];
                    }
                    break;

                default:
                    if(class_exists($fieldType)) {
                        $obj = new $fieldType($fieldIndexedArray[$fieldName]);
                        $mapValues[$propertyName] = $obj;
                    }
                    else {
                        $mapValues[$propertyName] = $fieldIndexedArray[$fieldName];
                    }
            }
        }

        return $mapValues;
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