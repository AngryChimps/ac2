<?php
/**
 * Created by PhpStorm.
 * User: sean
 * Date: 6/17/14
 * Time: 2:51 PM
 */

//use Doctrine\Common\ClassLoader;
//
//require '../vendor/doctrine/common/lib/Doctrine/Common/ClassLoader.php';
//
spl_autoload_register(function ($class) {
    $class_parts = explode('\\', $class);

    if($class_parts[0] == 'norm') {
        require_once(__DIR__ . '/../../' . implode($class_parts, DIRECTORY_SEPARATOR) . '.php');
    }
    elseif($class_parts[0] == 'Handlebars') {
        require_once(__DIR__ . '/../vendor/xamin/handlebars.php/src/' . implode($class_parts, DIRECTORY_SEPARATOR) . '.php');
    }

});

//$classLoader = new ClassLoader('Doctrine');
//$classLoader->register();
