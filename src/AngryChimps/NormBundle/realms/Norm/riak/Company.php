<?php

namespace Norm\riak;

use Norm\riak\base\CompanyBase;

class Company extends CompanyBase {
    public function __construct()
    {
        parent::__construct();

        $companyPhotos = new CompanyPhotos();
        $companyPhotos->companyId = $this->id;
        $companyPhotos->photos = array();
        $companyPhotos->save();

        $companyReviews = new CompanyReviews();
        $companyReviews->companyId = $this->id;
        $companyReviews->reviewIds = array();
        $companyReviews->save();

        $companyServices = new CompanyServices();
        $companyServices->companyId = $this->id;
        $companyServices->services = new ServiceCollection();
        $companyServices->save();
    }

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