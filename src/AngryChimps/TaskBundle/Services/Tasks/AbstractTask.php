<?php


namespace AngryChimps\TaskBundle\Services\Tasks;


use AngryChimps\GeoBundle\Services\GeolocationService;
use AngryChimps\NormBundle\services\NormService;

abstract class AbstractTask {
    /** @var  NormService */
    protected $norm;

    /** @var  GeolocationService */
    protected $geoService;

    public function setServices(NormService $norm, GeolocationService $geoService) {
        $this->norm = $norm;
        $this->geoService = $geoService;
    }

    public function createMysqlObj($normObj) {
        $class_parts = explode('\\', get_class($normObj));
        $function = 'get' . $class_parts[count($class_parts - 1)];
        $mysqlObj = $this->norm->$function($normObj->mysql_id);

        foreach($normObj as $fieldName => $value) {
            if(property_exists($mysqlObj, $fieldName)) {
                $mysqlObj->$fieldName = $value;
            }
        }
        $this->norm->create($mysqlObj);

        $normObj->mysql_id = $mysqlObj->mysql_id;
        $this->norm->update($normObj);
    }

    public function updateMysqlObj($normObj, array $changes) {
        $class_parts = explode('\\', get_class($normObj));
        $function = 'get' . $class_parts[count($class_parts - 1)];
        $mysqlObj = $this->norm->$function($normObj->mysql_id);

        $changed = false;
        foreach($changes as $fieldName => $value) {
            if(property_exists($mysqlObj, $fieldName)) {
                $changed = true;
                $mysqlObj->$fieldName = $value;
            }
        }

        if($changed) {
            $this->norm->update($mysqlObj);
        }
   }
}