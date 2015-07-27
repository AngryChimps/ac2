<?php


namespace AngryChimps\NormBundle\realms\Norm\riak\services;

use AC\NormBundle\cached\realms\riak\services\NormRiakBaseService;

class NormRiakService extends NormRiakBaseService {
//    public function create($obj)
//    {
//        if(get_class($obj) == 'Norm\\riak\\Member') {
//            $tableInfo = $this->realmInfo->getTableInfo(get_class($obj));
//
//            //Setup Debugging
//            if ($this->debug) {
//                $debug = $this->dataCollector->startCreateQuery($obj);
//            }
//            else {
//                $debug = null;
//            }
//
//            $class = get_class($obj);
//            $indexes = [['email_bin', $obj->email]];
//            $ds = $this->datastoreService->getDatastore($this->realmInfo->getDatastore($class));
//            $ds->createObjectWithIndexes($obj, $indexes, $debug);
//
//            //Store debugging data
//            if ($this->debug) {
//                $this->dataCollector->endQuery($debug);
//            }
//        }
//        else {
//            parent::create($obj);
//        }
//    }

//    public function update($obj)
//    {
//        if(get_class($obj) == 'Norm\\riak\\Member') {
//            $tableInfo = $this->realmInfo->getTableInfo(get_class($obj));
//            $data = $this->getAsArray($obj);
//            $bucket = $this->getBucket($tableInfo['realmName'], $tableInfo['name']);
//            $key = $this->getKeyName($this->getIdentifier($obj));
//            $data = json_encode($data);       // Read back the object from Riak
//
//            $response = $bucket->get($key);
//
//            // Make sure we got an object back
//            if ($response->hasObject()) {
//                // Get the first returned object
//                $readObject = $response->getFirstObject();
//            }
//            else {
//                throw new \Exception('Original object not found; unable to update.');
//            }
//            $readObject->setContent($data);
//            $bucket->addIndex('email_bin', 'bin', 'email')
//                ->put($readObject);
//
//
//        }
//        else {
//            parent::update($obj);
//        }
//    }

    public function getMemberByEmail($email) {
        return $this->getObjectBySecondaryIndex('Norm\\riak\\Member', 'email_bin',
            $email);
    }
}