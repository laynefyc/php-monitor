<?php
return [
    'debug' => false,
    /*
     * support extension: uprofiler, tideways_xhprof, tideways, xhprof
     * default: tideways
     */
    'extension' => 'tideways',
    // 'save' => [
    //     'driver'    => 'mysql',
    //     'host'      => '127.0.0.1:3306',
    //     'database'  => 'php_monitor',
    //     'username'  => '',
    //     'password'  => 'abcd1234',
    //     'charset'   => 'utf8mb4',
    //    'options' => [
    //        1005 => 16777216, //PDO::MYSQL_ATTR_MAX_BUFFER_SIZE and 16M
    //    ]
    // ],
    //  'save' => [
    //      'driver'    => 'mongodb',
    //      'host'      => '127.0.0.1:27017',
    //      'database'  => 'php_monitor',
    //      'username'  => '',
    //      'password'  => ''
    //  ],
   'save' => [
       'driver'    => 'sqlite',
       'database'  =>  dirname(__DIR__).'/db/php_monitor.sqlite3'
   ],
    'profiler' => [
        'enable' => function() {
            return true;//rand(1, 100) > 0;
        },
        'filter_path' => [
            //filter the DOCUMENT_ROOT
            //'/home/www/xhgui/webroot','F:/phpPro'
        ]
    ],
    'user' => [
        //login account and password
        ['account'=>'php','password'=>'php'],
        ['account'=>'admin','password'=>'abcd']
    ]
];