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
        $mem->first = 'Bob';
        $mem->last = 'Bobbington';
        $mem->email = 'test@test.com';

        return $mem;
    }

    /**
     * @return \NormTests\riak\Member
     */
    public static function getNewSavedObject() {
        $mem = self::getNewUnsavedObject();
        $mem->save();
        return $mem;
    }

    public function testSaveNew() {
        $mem = self::getNewUnsavedObject();
        $mem->save();

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
        $mem = self::getNewUnsavedObject();
        $id = $mem->id;
        $mem->save();
        $mem->invalidate();

        $normObj = Member::getByPk($id);
        $normObj->first = 'Bill';
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