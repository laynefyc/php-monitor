# php-monitor
新PHP非侵入式监控平台- 只能用来优化性能，定位Bug，分析请求。

## 使用教程

1. 下载&更新项目

	````bash
	git clone https://github.com/laynefyc/php-monitor.git
	cd php-monitor
	composer update
	````
2. 设置数据存储方式（支持MySQL，MongoDB，Sqlite）
	
	如果使用MySQL请先运行如下建表语句：
	
	````sql
	CREATE TABLE `php_monitor` (
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增编号',
		`url` text CHARACTER SET utf8 COMMENT '请求URL',
		`server_name` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '服务名',
		`get` text COMMENT 'GET参数',
		`pmu` int(11) unsigned DEFAULT NULL COMMENT '内存峰值',
		`wt` int(11) unsigned DEFAULT NULL COMMENT '总耗时微秒',
		`cpu` int(11) unsigned DEFAULT NULL COMMENT '总cpu周期时间',
		`ct` int(3) NOT NULL COMMENT '总调用次数',
		`mu` int(11) unsigned DEFAULT NULL COMMENT '当前内存消耗',
		`request_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '请求时间，到秒',
		`request_time_micro` int(10) unsigned DEFAULT '0' COMMENT '请求微秒',
		`profile` longblob NOT NULL COMMENT '性能数据',
		`server` longblob COMMENT 'SERVER参数',
		`type` varchar(16) DEFAULT NULL COMMENT '请求类型GET，POST',
		`ip` varchar(16) DEFAULT NULL COMMENT 'IP地址',
		PRIMARY KEY (`id`),
		KEY `idx_url` (`url`),
		KEY `idx_ip` (`ip`)
	) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
	````
	修改配置文件`src/config/config.php`
	
	````php
	// 'save' => [
    //     'driver'    => 'mysql',
    //     'host'      => '127.0.0.1:3306',
    //     'database'  => 'php_monitor',
    //     'username'  => '',
    //     'password'  => 'abcd1234',
    //     'charset'   => 'utf8mb4'
    // ],
    'save' => [
        'driver'    => 'mongodb',
        'host'      => '127.0.0.1:27017',
        'database'  => 'php_monitor',
        'username'  => '',
        'password'  => ''
    ],
    // 'save' => [
    //     'driver'    => 'file',
    //     'database'  =>  dirname(__DIR__).'/db/pmonitor.data'
    // ],
	````
	配置文件写的很清晰，这里不做过多解释。所有数据存储方式的表名都为`php_monitor`。
	
	（非必须）如果使用的是MongoDB，可以在数据插入成功后添加索引（MongoDB会自己建表，所以索引要手动添加），添加方式如下：
	
	````bash
	show dbs
	use php_monitor //数据库选择你自己的
	db.php_monitor.createIndex({"url":1})
	db.php_monitor.createIndex({"ip":1})
	````
4. 项目中埋点文件
	
	在项目中埋点有两种方式，一是通过Nginx配置：
	
	````nginx
	server {
	  listen 80;
	  server_name site.localhost;
	  root your/webroot/; 
	  fastcgi_param PHP_VALUE "auto_prepend_file={php-monitor-path}/src/autoPrepend.php";
	}
	````
	Nginx的这种方式本质上是在调用PHP提供的`auto_prepend_file`接口，接口文档 [https://www.php.net/manual/zh/ini.core.php#ini.auto-prepend-file](https://www.php.net/manual/zh/ini.core.php#ini.auto-prepend-file)，需要重启nginx和清空opcache.
	
	第二种方式是直接在需要监控项目的入口文件加载，通常是在`index.php`中添加：
	
	````php
	<?php
	use pm\common\Router;
	
	//核心代码在此
	require '/home/www/cai/php-monitor/src/autoPrepend.php';
	
	include 'vendor/autoload.php';
	$config = require('src/config/config.php');
	(new Router($config))->run();
	````
	添加埋点之后，访问要监控的项目，分析数据就会被存储。
	
5. 运行本监控平台

	最简单的运行方式如下：
	
	````
	cd php-monitor
	php -S 127.0.0.1:8066
	````
	运行成功后请访问 [http://127.0.0.1:8066/public](http://127.0.0.1:8066/public)
	
## TODO
1. 完善国际化；
2. 完善文档；
3. 重写xhprof扩展；
4. Sqlite存储方式开发；
	
## 反馈
提交ISSUE或者加我微信

![https://github.com/laynefyc/xhgui-branch/blob/screenshot/screenshot/code-log1.png](https://github.com/laynefyc/xhgui-branch/blob/screenshot/screenshot/code-log1.png)

[http://imgs.it2048.cn/code-log.png](http://imgs.it2048.cn/code-log.png)

大家的反馈是我更新的动力