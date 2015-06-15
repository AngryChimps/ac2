<?php
/**
 * Created by PhpStorm.
 * User: sean
 * Date: 6/18/14
 * Time: 1:13 PM
 */

namespace AC\NormBundle\core\datastore;

use AC\NormBundle\core\Utils;

class Riak1BlobDatastore extends AbstractRiak1Datastore {
    public function createObject($obj, &$debug)
    {
        $tableInfo = $this->realmInfo->getTableInfo(get_class($obj));

        //Deal with created_at if necessary
        if(property_exists($obj, 'createdAt')) {
            $obj->createdAt = new \DateTime();
        }
        $data = $this->getAsArray($obj);
        $bucket = $this->getBucket($tableInfo['realmName'], $tableInfo['name']);
        $key = $this->getKeyName($this->getIdentifier($obj));
        $data = json_encode($data);


        if($debug !== null) {
            $arr = [];
            $arr['bucket'] = $bucket->getName();
            $arr['key'] = $key;
            $arr['data'] = $data;
            $debug['createObject'][] = $arr;
            $this->loggerService->info('Creating object: ' . json_encode($debug));
        }

        $riakObj = new \Riak\Object($key);
        $riakObj->setContent($data);
        $bucket->put($riakObj);
    }

    public function createObjectWithIndexes($obj, $indexes, &$debug) {
        $tableInfo = $this->realmInfo->getTableInfo(get_class($obj));

        //Deal with created_at if necessary
        if(property_exists($obj, 'createdAt')) {
            $obj->createdAt = new \DateTime();
        }
        $data = $this->getAsArray($obj);
        $bucket = $this->getBucket($tableInfo['realmName'], $tableInfo['name']);
        $key = $this->getKeyName($this->getIdentifier($obj));
        $data = json_encode($data);


        if($debug !== null) {
            $arr = [];
            $arr['bucket'] = $bucket->getName();
            $arr['key'] = $key;
            $arr['data'] = $data;
            $debug['createObject'][] = $arr;
            $this->loggerService->info('Creating object: ' . json_encode($debug));
        }

        $riakObj = new \Riak\Object($key);
        $riakObj->setContent($data);

        foreach($indexes as $index) {
            if ($index[1] !== null) {
                $riakObj->addIndex($index[0], $index[1]);
            }
        }
        $bucket->put($riakObj);

    }

    public function createCollection($coll, &$debug)
    {
        for($i = 0; $i < count($coll); $i++) {
            $this->createObject($coll[$i], $debug);
        }
    }

    public function updateObject($obj, &$debug)
    {
        $tableInfo = $this->realmInfo->getTableInfo(get_class($obj));

        //Deal with times if necessary
        if(property_exists($obj, 'updatedAt')) {
            $obj->updatedAt = new \DateTime();
        }

        $data = $this->getAsArray($obj);
        $bucket = $this->getBucket($tableInfo['realmName'], $tableInfo['name']);
        $key = $this->getKeyName($this->getIdentifier($obj));
        $data = json_encode($data);

        if($debug !== null) {
            $arr = [];
            $arr['bucket'] = $bucket->getName();
            $arr['key'] = $key;
            $debug['updateObject'][] = $arr;
            $this->loggerService->info('Updating object: ' . json_encode($debug));
        }

        // Read back the object from Riak
        $response = $bucket->get($key);

        // Make sure we got an object back
        if ($response->hasObject()) {
            // Get the first returned object
            $readObject = $response->getFirstObject();
        }
        else {
            throw new \Exception('Original object not found; unable to update.');
        }
        $readObject->setContent($data);
        $bucket->put($readObject);
    }

    public function updateCollection($coll, &$debug)
    {
        for($i = 0; $i < count($coll); $i++) {
            $this->updateObject($coll[$i], $debug);
        }
    }

    public function deleteObject($obj, &$debug)
    {
        $tableInfo = $this->realmInfo->getTableInfo(get_class($obj));
        $bucket = $this->getBucket($tableInfo['realmName'], $tableInfo['name']);
        $key = $this->getKeyName($this->getIdentifier($obj));

        if($debug !== null) {
            $arr = [];
            $arr['bucket'] = $bucket->getName();
            $arr['key'] = $key;
            $debug['deleteObject'][] = $arr;
            $this->loggerService->info('Deleting object: ' . json_encode($debug));
        }

        // Read back the object from Riak
        $response = $bucket->get($key);

        // Make sure we got an object back
        if ($response->hasObject()) {
            // Get the first returned object
            $readObject = $response->getFirstObject();
        }
        else {
            throw new \Exception('Original object not found; unable to update.');
        }

        $bucket->delete($readObject);
    }

    public function deleteCollection($coll, &$debug)
    {
        for($i = 0; $i < count($coll); $i++) {
            $this->deleteObject($coll[$i], $debug);
        }
    }

//    protected function getAsArray($obj) {
//        if(is_array($obj)) {
//            return $obj;
//        }
//
//        $tableInfo = $this->realmInfo->getTableInfo(get_class($obj));
//        $arr = [];
//
//        if($this->isCollection($obj)) {
//            foreach($obj as $object) {
//                $arr[] = $this->getAsArray($object);
//            }
//        }
//        else {
//            for ($i = 0; $i < count($tableInfo['fieldNames']); $i++) {
//                if ($obj->{$tableInfo['propertyNames'][$i]} === null) {
//                    $arr[$tableInfo['fieldNames'][$i]] = null;
//                } else {
//                    switch ($tableInfo['fieldTypes'][$i]) {
//                        case 'Date':
//                            $arr[$tableInfo['fieldNames'][$i]] = $obj->{$tableInfo['propertyNames'][$i]}->format('Y-m-d');
//                            break;
//                        case 'DateTime':
//                            $arr[$tableInfo['fieldNames'][$i]] = $obj->{$tableInfo['propertyNames'][$i]}->format('Y-m-d H:i:s');
//                            break;
//                        default:
//                            if (class_exists($tableInfo['fieldTypes'][$i])) {
//                                $arr[$tableInfo['fieldNames'][$i]] = $this->getAsArray($obj->{$tableInfo['propertyNames'][$i]});
//                            } else {
//                                $arr[$tableInfo['fieldNames'][$i]] = $obj->{$tableInfo['propertyNames'][$i]};
//                            }
//                    }
//                }
//            }
//        }
//
//        return $arr;
//    }

    public function getAsArray($obj)
    {
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

    public function populateObjectByPks($obj, $pks, &$debug)
    {
        $tableInfo = $this->realmInfo->getTableInfo(get_class($obj));
        $bucket = $this->getBucket($tableInfo['realmName'], $tableInfo['name']);
        $key = $this->getKeyName($pks);

        if($debug !== null) {
            $arr = [];
            $arr['bucket'] = $bucket->getName();
            $debug['populateObjectByPks'][] = $arr;
            $this->loggerService->info('Populating object by primary keys: ' . json_encode($debug));
        }

        // Read back the object from Riak
        $response = $bucket->get($key);

        if(!is_object($response)) {
            $this->loggerService->info('Riak returned a response which was not an object.');
            return false;
        }

        // Make sure we got an object back
        if ($response->hasObject()) {
            // Get the first returned object
            $content = $response->getFirstObject();
            $json = $content->getContent();
            $arr = json_decode($json, true);
            $this->populateObjectWithArray($obj, $arr);
        }
        else {
            $this->loggerService->info('The response from Riak did not have an object.');
            return false;
        }
        return true;
    }


    public function populateCollectionByPks($coll, $pks, &$debug) {
        //For a collection $pks would be an array of ids or an array of an array of ids
        $tableInfo = $this->realmInfo->getTableInfo(get_class($coll));

        foreach($pks as $pk) {
            $object = new $tableInfo['objectName']();

            if($this->populateObjectByPks($object, $pk, $debug) === false) {
                throw new \Exception('Unable to find one or more objects to populate the collection.');
            }

            $coll[$this->getIdentifier($object)] = $object;
        }
    }

    public function populateObjectBySecondaryIndex($obj, $indexName, $value, &$debug = null) {
        $tableInfo = $this->realmInfo->getTableInfo(get_class($obj));
        $bucket = $this->getBucket($tableInfo['realmName'], $tableInfo['name']);
//        $key = $this->getKeyName($pks);

        $response = $bucket->index($indexName, $value);

        if(empty($response)) {
            return false;
        }

        if($response->hasObject()) {
            $content = $response->getFirstObject();
            $json = $content->getContent();
            $this->populateObjectByOrderedArray($obj, json_decode($json, true));
            return true;
        }
        else {
            return false;
        }
    }

//    public function populateCollectionBySecondaryIndex(NormBaseCollection $coll, $indexName, $value, &$debug = null) {
//        $bucket = $this->getBucket($coll::$realm, $coll::$tableName);
//
//        $response = $bucket->index($indexName, $value);
//
//        if(empty($response)) {
//            return null;
//        }
//
//        if($response->hasObject()) {
//            $content = $response->getFirstObject();
//            $json = $content->getContent();
//            $this->populateCollectionByOrderedArray($coll, json_decode($json, true));
//        }
//        else {
//            return null;
//        }
//    }
//
//    protected function populateObjectByOrderedArray(NormBaseObject $obj, array $arr) {
//        for($i = 0; $i < count($obj::$fieldNames); $i++) {
//            $fieldType = $obj::$fieldTypes[$i];
//            $propertyName = $obj::$propertyNames[$i];
//            $value = $arr[$i];
//
//            if(class_exists($fieldType) && in_array("AC\\NormBundle\\core\\NormBaseObject", class_parents($fieldType))) {
//                $object = new $fieldType();
//                $this->populateObjectByOrderedArray($object, json_decode($value));
//                $obj->$propertyName = $object;
//            }
//            elseif(class_exists($fieldType) && in_array("AC\\NormBundle\\core\\NormBaseCollection", class_parents($fieldType))) {
//                $object = new $fieldType();
//                $this->populateObjectByOrderedArray($object, json_decode($value));
//                $obj->$propertyName = $object;
//            }
//            else {
//                switch($fieldType) {
//                    case 'int':
//                        $obj->$propertyName = (int) $value;
//                        break;
//                    case 'bool':
//                        $obj->$propertyName = (bool) $value;
//                        break;
//                    case 'float':
//                        $obj->$propertyName = (float) $value;
//                        break;
//                    case 'Date':
//                    case 'DateTime':
//                        $obj->$propertyName = new \DateTime($value);
//                        break;
//                    case 'int[]':
//                    case 'float[]':
//                    case 'double[]':
//                    case 'string[]':
//                        $obj->$propertyName = $value;
//                        break;
//                    default:
//                        $obj->$propertyName = $value;
//                }
//            }
//
//        }
//    }
//
//    protected function populateCollectionByOrderedArray(NormBaseCollection $coll, array $arr) {
//        foreach($arr as $objArr) {
//            $objectClass = $coll::$singularClassName;
//            $obj = new $objectClass();
//            $this->populateObjectByOrderedArray($obj, $objArr);
//            $coll[$this->getIdentifier($obj)] = $obj;
//        }
//    }

}