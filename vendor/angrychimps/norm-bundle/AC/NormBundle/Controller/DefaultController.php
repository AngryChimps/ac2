<?php

namespace AC\NormBundle\Controller;

use AC\NormBundle\Services\RealmInfoService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function generateAction()
    {
        $test = $this->get('ac_norm.realm_info');
    }
}
