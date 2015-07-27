<?php


namespace AC\NormBundle\Services\realmInfoClasses;


class Norm {
    public $debug;

    private $realms;
    private $datastores;

    /**
     * @param $realmName string
     * @return Realm
     */
    public function getRealm($realmName) {
        return $this->realms[$realmName];
    }

    /**
     * @param $datastoreName
     * @return Datastore
     */
    public function getDatastore($datastoreName) {
        return $this->datastores[$datastoreName];
    }

    /**
     * @param $realmName
     * @return Datastore
     */
    public function getPrimaryDatastore($realmName) {
        return $this->datastores[$this->realms[$realmName]];
    }

    public function addRealm(Realm $realm) {
        $this->realms[$realm->name] = $realm;
    }

    public function addDatastore(Datastore $datastore) {
        $this->datastores[$datastore->name] = $datastore;
    }
} 