<?php
// If the environment is set to prod, load app.php, otherwise load app_ENV.php
// If there is no environment variable set, load app_local.php

$env = $_SERVER['AC_ENV'];

if(empty($env)){
    require "app_local.php";
}
elseif($env === 'prod') {
    require "app.php";
}
else {
    require "app_" . $env . ".php";
}
