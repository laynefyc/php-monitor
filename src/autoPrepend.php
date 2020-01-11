<?php
use pm\common\PMonitor;
include __DIR__ . '/../vendor/autoload.php';
$config = require('config/config.php');
PMonitor::loadConfig($config);
register_shutdown_function(
    function(){
        PMonitor::shutdown();
    }
);