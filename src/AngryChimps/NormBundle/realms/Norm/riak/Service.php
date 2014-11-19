<?php

namespace Norm\riak;

use Norm\riak\base\ServiceBase;

class Service extends ServiceBase {
    public function getPublicArray() {
        $arr = array();
        $arr['name'] = $this->name;
        $arr['discounted_price'] = $this->discountedPrice;
        $arr['original_price'] = $this->originalPrice;
        $arr['mins_for_service'] = $this->minsForService;
        $arr['category_id'] = $this->categoryId;
        return $arr;
    }

    public function getPrivateArray() {
        $arr = $this->getPublicArray();
        $arr['mins_notice'] = $this->minsNotice;
        return $arr;
    }
}