<?php
/**
 * Created by PhpStorm.
 * User: sean
 * Date: 6/17/14
 * Time: 2:33 PM
 */

$config = \norm\config\Config::getInstance();

if($config->databases == NULL || count($config->databases) < 1) {
    
}