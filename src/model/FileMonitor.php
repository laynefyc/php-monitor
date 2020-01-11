<?php
namespace pm\model;
class FileMonitor{
    
    public static $_file = "../db/pmonitor.data";
    public function insertData($data){
        file_put_contents(self::$_file,serialize($data).PHP_EOL,FILE_APPEND);
    }
}