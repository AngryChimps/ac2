<?php


namespace AC\NormBundle\Tests\realms\NormTests\mysql\test;

use NormTests\mysql\Address;
use NormTests\mysql\AddressCollection;
use AC\NormBundle\Tests\AbstractMysqlTestCase;

class AddressCollectionTest extends AbstractMysqlTestCase {

    /**
     * @return AddressCollection
     */
    public function testCreateNew() {
        $coll = new AddressCollection();

        for($i=0; $i<2; $i++) {
            $obj = new Address();
            $obj->street = 'street address ' . $i;
            $obj->city = 'city here';
            $obj->state = 'ST';
            $obj->zip = 12345;

            $coll[] = $obj;
        }

        assertEquals(2, count($coll));

        return $coll;
    }

    /**
     * @depends testCreateNew
     */
    public function testSaveNew(AddressCollection $coll) {
        $currentRowCount = $this->getConnection()->getRowCount('address');

//        $coll->save();
        foreach($coll as $obj) {
            $obj->save();
        }
        assertEquals($currentRowCount + 2, $this->getConnection()->getRowCount('address'));
    }
}
 