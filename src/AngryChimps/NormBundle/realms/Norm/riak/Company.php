<?php

namespace Norm\riak;

use Norm\riak\base\CompanyBase;

class Company extends CompanyBase {
    public function getPublicArray() {
        $arr = array();
        $arr['id'] = $this->id;
        $arr['name'] = $this->name;
        $arr['mysql_id'] = $this->mysqlId;
        return $arr;
    }

    public function getPrivateArray() {
        $arr = $this->getPublicArray();

        return $arr;
    }

    /**
     * @param $pk
     * @return Company|null
     */
    public static function getByPkEnabled($pk) {
        $company = self::getByPk($pk);

        if($company === null || $company->status !== self::ENABLED_STATUS) {
            return null;
        }

        return $company;
    }

}