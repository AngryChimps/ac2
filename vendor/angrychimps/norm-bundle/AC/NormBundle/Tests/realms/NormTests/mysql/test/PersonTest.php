<?php


namespace AC\NormBundle\Tests\realms\NormTests\mysql\test;

use AC\NormBundle\core\NormBaseObject;
use AC\NormBundle\Tests\AbstractMysqlTestCase;
use NormTests\mysql\Company;
use NormTests\mysql\Person;
use NormTests\mysql\Address;

class PersonTest extends AbstractMysqlTestCase {

    /**
     * @depends AddressTest::saveAddress
     * @return Person
     */
    public static function getNewTestPerson() {
        $addr = AddressTest::getNewTestAddressAfterSaving();
        $p = new Person();
        $p->addressId = $addr->id;

        return $p;
    }

    /**
     * @depends getNewTestPerson
     * @return Person
     */
    public static function getNewTestPersonAfterSaving() {
        $p = self::getNewTestPerson();
        $p->save();


        return $p;
    }

    public function testLoadMother() {
        //Create two people, a child and a mother
        $p1 = self::getNewTestPersonAfterSaving();
        $p2 = self::getNewTestPersonAfterSaving();

        //Assign the mother
        $p1->motherId = $p2->id;
        $p1->save();

        //Clear the local cache so everything is a fresh read
        NormBaseObject::invalidateAll();

        //Reload $p1 and then load its mother
        $child = Person::getByPk($p1->id);

    }

    public function testDelete() {
        $person = self::getNewTestPersonAfterSaving();
        $id = $person->id;
        $person->delete();
        $person->invalidate();

        $person2 = Person::getByPk($id);

        assertNull($person2);
    }

 }
 