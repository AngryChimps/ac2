<?php

namespace Norm\riak;

use Norm\riak\base\LocationBase;

class Location extends LocationBase {
    public function getPublicArray() {
        $arr = array();
        $arr['id'] = $this->id;
        $arr['company_id'] = $this->companyId;
        $arr['name'] = $this->name;
        $arr['street1'] = $this->street1;
        $arr['street2'] = $this->street2;
        $arr['city'] = $this->city;
        $arr['state'] = $this->state;
        $arr['zip'] = $this->zip;
        $arr['lat'] = $this->lat;
        $arr['long'] = $this->long;
        $arr['phone'] = $this->phone;
        $arr['photos'] = $this->photos;

        return $arr;
    }
    public function getPrivateArray() {
        return $this->getPublicArray();
    }
}