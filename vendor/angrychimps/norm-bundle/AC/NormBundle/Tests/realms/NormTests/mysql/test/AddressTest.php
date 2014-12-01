<?php

namespace AC\NormBundle\Tests\realms\NormTests\mysql\test;

use NormTests\mysql\Address;
use AC\NormBundle\Tests\AbstractMysqlTestCase;

class AddressTest extends AbstractMysqlTestCase {

    public static function getNewTestAddress() {
        $obj = new Address();
        $obj->street = 'street address';
        $obj->city = 'city here';
        $obj->state = 'ST';
        $obj->zip = 12345;

        return $obj;
    }

    public static function getNewTestAddressAfterSaving() {
        $addr = self::getNewTestAddress();
        $addr->save();
        self::addObjectForCleanup($addr);

        return $addr;
    }

    public function testSaveNew()
    {
        $obj = self::getNewTestAddress();

        $currentRowCount = $this->getConnection()->getRowCount('address');

        $obj->save();

        assertEquals($currentRowCount + 1, $this->getConnection()->getRowCount('address'));
    }
}
 