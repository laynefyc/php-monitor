<?php
use pm\common\Router;

if(empty($_GET)){
    header("Location: /assets/",TRUE,301);
}
include '../vendor/autoload.php';
$config = require('../src/config/config.php');
(new Router($config))->run();



   