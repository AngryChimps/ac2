<?php


namespace AC\NormBundle\core;


class NormObjectLocalStore {
    private static $_objects = array();

    public static function add(NormBaseObject $nbo) {
        $identifier = self::getIdentifier($nbo->getPrimaryKeyData());
        $realm = $nbo->getRealm();

        if(!isset(self::$_objects[$realm])) {
            return null;
        }
        if(!isset(self::$_objects[$realm][get_class($nbo)])) {
            self::$_objects[$realm][get_class($nbo)] = array();
        }

        self::$_objects[$realm][get_class($nbo)][$identifier] = $nbo;
    }

    public static  function get($realm, $class, $primaryKeyData) {
        $identifier = self::getIdentifier($primaryKeyData);
        if(!isset(self::$_objects[$realm])) {
            return null;
        }
        if(!isset(self::$_objects[$realm][$class])) {
            return null;
        }

        return self::$_objects[$realm][$class][$identifier];
    }

    public static function invalidate($realm, $class, $primaryKeyData) {
        $identifier = self::getIdentifier($primaryKeyData);
        self::$_objects[$realm][$class][$identifier] = null;
    }

    public static function invalidateAll() {
        self::$_objects = null;
    }

    protected static function getIdentifier($pks) {
        $pks = array_values($pks);
        return implode('|', $pks);
    }
} 