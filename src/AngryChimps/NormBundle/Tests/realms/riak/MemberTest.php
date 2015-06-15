<?php


namespace AngryChimps\NormBundle\Tests\realms\riak;

use AC\NormBundle\Tests\AbstractRiakTestCase;
use Norm\riak\Member;

class MemberTest extends AbstractRiakTestCase {
    /**
     * @return \Norm\riak\Member
     */
    public static function getNewUnsavedObject() {
        $mem = new Member();

        $mem->setFname('Bob');
        $mem->setLname('Bobberson');
        $mem->setEmail('norm_test_' . rand(1, 999999999) . '@seangallavan.com');
        $mem->setDob(new \DateTime('1949-01-01'));

        return $mem;
    }


}