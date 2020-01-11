<?php
use pm\common\Router;

include 'vendor/autoload.php';
$config = require('src/config/config.php');
(new Router($config))->run();



   