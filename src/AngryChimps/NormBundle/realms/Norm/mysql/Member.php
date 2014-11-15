<?php

namespace Norm\mysql;

use Norm\mysql\base\MemberBase;

class Member extends MemberBase {

    /**
     * @param $id
     * @return \Norm\mysql\Member
     */
    public static function getById($id) {
        return self::getByWhere('id = :id', array(':id' => $id));
    }

    /**
     * @param $email
     * @return \Norm\mysql\Member
     */
    public static function getByEmail($email) {
        return self::getByWhere('email = :email', array(':email' => $email));
    }

}