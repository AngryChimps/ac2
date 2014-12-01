<?php

namespace AC\NormBundle\Tests\realms\riak;

use NormTests\riak\Member;
use AC\NormBundle\Tests\AbstractRiakTestCase;

class MemberTest extends AbstractRiakTestCase {
    /**
     * @return \NormTests\riak\Member
     */
    public static function getNewUnsavedObject() {
        $mem = new Member();
        $mem->fname = 'Bob';
        $mem->lname = 'Bobbington';
        $mem->email = 'trashy' . rand(1, 999999999) . '@seangallavan.com';
        $mem->dob = new \DateTime('1949-01-01');

        return $mem;
    }

    /**
     * @return \NormTests\riak\Member
     */
    public static function getNewSavedObject() {
        $mem = self::getNewUnsavedObject();
        $mem->save();
        self::addObjectForCleanup($mem);
        return $mem;
    }

    public function testSaveNew() {
        $mem = self::getNewUnsavedObject();
        $mem->save();
        self::addObjectForCleanup($mem);

        $bucket = $this->getObjectsBucket('member');
        $response = $bucket->get($mem->id);

        if(!empty($response)) {
            assertTrue($response->hasObject());

            $content = $response->getFirstObject();
            $dbObj = $content->getContent();
            $dbObjDecompressed = json_decode($dbObj);

            assertEquals($dbObjDecompressed->id, $mem->id);
        }
    }

    public function testSaveExisting() {
        $mem = self::getNewSavedObject();
        $id = $mem->id;
        $mem->invalidate();

        $normObj = Member::getByPk($id);
        $normObj->fname = 'Bill';
        $normObj->save();

        $bucket = $this->getObjectsBucket('member');
        $response = $bucket->get($id);

        assertTrue($response->hasObject());

        $content = $response->getFirstObject();
        $dbObj = $content->getContent();
        $dbObjDecompressed = json_decode($dbObj);

        assertEquals($dbObjDecompressed->id, $normObj->id);
    }

    public function testDelete() {
        $mem = self::getNewSavedObject();
        $id = $mem->id;
        $mem->delete();

        $mem2 = Member::getByPk($id);

        assertNull($mem2);
    }
}