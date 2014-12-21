<?php


namespace AngryChimps\NormBundle\realms\Norm\mysql\services;

use AC\NormBundle\cached\realms\mysql\services\NormMysqlBaseService;

class NormMysqlService extends NormMysqlBaseService {
    public function getMemberByEmail($email) {
        return $this->getMemberByWhere('email = :email', array(':email' => $email));
    }
}