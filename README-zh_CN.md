<h1 align="center">php-monitor</h1>

<div align="center">
一个免费、易用、强大的PHP服务监控工具。帮助开发者监控PHP服务，分析PHP性能。

[![Latest Stable Version](https://poser.pugx.org/laynefyc/php-monitor/v/stable.png)](https://packagist.org/packages/laynefyc/php-monitor)
[![Total Downloads](https://poser.pugx.org/laynefyc/php-monitor/downloads.png)](https://packagist.org/packages/laynefyc/php-monitor)
[![Build Status](https://travis-ci.org/laynefyc/php-monitor.svg?branch=master)](https://travis-ci.org/laynefyc/php-monitor)
</div>

![home](https://raw.githubusercontent.com/laynefyc/php-monitor/screenshot/screenshot/home.png)

![](https://raw.githubusercontent.com/laynefyc/php-monitor/screenshot/screenshot/infomation.png)

![flame](https://raw.githubusercontent.com/laynefyc/php-monitor/screenshot/screenshot/flame.png)

![url](https://raw.githubusercontent.com/laynefyc/php-monitor/screenshot/screenshot/url.png)


[English](./README.md) | 简体中文
## ✨ 特性

- 🌈 获取详细的PHP运行时数据。
- 🌍 监控生产环境耗时请求。
- 🛡 显示底层函数的内存与CPU消耗。
- 🎨 运用各种可视化图形显示数据。

## ⚙️ 系统要求

- uprofiler,xhprof,tideways等扩展（安装多个会有冲突，默认安装tideways）
- composer
- PHP 5.6+

## tideways扩展安装
如果使用PHP 5.6，请下载tideways v4.1.5。如果使用PHP7+ ，请下载v4.1.7（更高的版本无法显示SQL）。

````bash
wget --no-check-certificate https://github.com/tideways/php-xhprof-extension/archive/v4.1.7.tar.gz  && tar zxvf v4.1.7.tar.gz && cd php-xhprof-extension-4.1.7 && phpize && ./configure && make && sudo make install

````

安装后需要在`php.ini`文件中添加扩展引入命令：

````bash
extension=tideways.so
````
通过如下命令可查看扩展是否安装成功：

````bash
> php --ri tideways
tideways
tideways => 4.1.7
````

## php-monitor服务安装

````bash
composer create-project --prefer-dist --ignore-platform-reqs laynefyc/php-monitor php-monitor && cd php-monitor/public && php -S 127.0.0.1:8066
````

访问 [http://127.0.0.1:8066](http://127.0.0.1:8066) 会要求输入账号和密码，默认都为 php

## 详细教程

1. 下载&更新项目

	````bash
	composer create-project --prefer-dist --ignore-platform-reqs laynefyc/php-monitor php-monitor
	````
	或者

	````bash
	git clone https://github.com/laynefyc/php-monitor.git
	cd php-monitor
	composer update --ignore-platform-reqs
	````
2. 设置数据存储方式，支持MySQL，MongoDB，Sqlite
	
	在配置文件`src/config/config.php`中设置，信息如下：
	
	````php
    // 'save' => [
    //     'driver'    => 'mysql',
    //     'host'      => '127.0.0.1:3306',
    //     'database'  => 'php_monitor',
    //     'username'  => '',
    //     'password'  => 'abcd1234',
    //     'charset'   => 'utf8mb4'
    // ],
    // 'save' => [
    //     'driver'    => 'mongodb',
    //     'host'      => '127.0.0.1:27017',
    //     'database'  => 'php_monitor',
    //     'username'  => '',
    //     'password'  => ''
    // ],
    'save' => [
        'driver'    => 'sqlite',
        'database'  =>  dirname(__DIR__).'/db/php_monitor.sqlite3'
    ],
	````
	本项目默认使用Sqlite，因为Sqlite是轻量级的文件数据库。如果使用其他数据库请取消对应的注释。
	
	使用MySQL请运行如下建表语句（表名不可修改）：
	
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
	
	使用MongoDB数据库会自己建表，但需要自己添加索引，添加方式如下：
	
	````bash
	show dbs
	use php_monitor //数据库选择你自己的
	db.php_monitor.createIndex({"url":1})
	db.php_monitor.createIndex({"ip":1})
	````
	所有数据存储方式的表名都必须为 `php_monitor` 不支持修改。
	
3. 运行本监控平台

	测试时可直接通过如下命令如下：
	````
	cd php-monitor/public
	php -S 127.0.0.1:8066
	````
	运行成功后直接访问 [http://127.0.0.1:8066](http://127.0.0.1:8066)
	
	非测试环境请使用Nginx，配置如下：
	
	````nginx
    server {
        listen       8066;
        server_name  localhost;
        root /home/www/cai/php-monitor/public;
        index  index.php index.html;
        location / {
            root /home/www/cai/php-monitor/public;
        }

        location ~ \.php$ {
            fastcgi_pass   127.0.0.1:9000;
            include        fastcgi_params;
            fastcgi_param  SCRIPT_FILENAME  $document_root/index.php;
        }
    }

    ````
4. 登录后台

    登录账号密码可以直接在配置文件中修改，`src/config/config.php`。

    ````
    'user' => [
        //login account and password
        ['account'=>'php','password'=>'php'],
        ['account'=>'admin','password'=>'abcd']
    ]
    ````
    account是帐号，password是密码，发布后请及时修改。
    如果对安全等级要求更高，请扩展 LoginController.php 文件的 accountAction 方法。
		
5. 在需要监控的项目中引入监控
	
	本项目采用非侵入式的方式进行项目监控，对运行中的服务不会有任何干扰。
	
	在项目中添加监控有两种方式，一是修改Nginx配置：
	
	比如要对运行中的服务 www.site.com 做监控，你只需要在Nginx配置文件中加一行配置信息
	
	````nginx
	fastcgi_param PHP_VALUE "auto_prepend_file={php-monitor-path}/src/autoPrepend.php";

    ````
    添加配置后的效果如下（其他内容只是为了演示说明，并不是要求你的nginx配置和我的一样）：
    
	````nginx
	server {
	  listen 80;
	  server_name www.site.com;
	  root your/webroot/; 
      location ~ \.php$ {
          fastcgi_pass   127.0.0.1:9000;
          include        fastcgi_params;
          fastcgi_param  SCRIPT_FILENAME  $document_root/index.php;
          fastcgi_param PHP_VALUE "auto_prepend_file={php-monitor-path}/src/autoPrepend.php";
      }
	}
	````
	这种方式是使用PHP提供的`auto_prepend_file`接口，接口文档 [https://www.php.net/manual/zh/ini.core.php#ini.auto-prepend-file](https://www.php.net/manual/zh/ini.core.php#ini.auto-prepend-file)，添加配置后需要重启nginx.
	
	第二种方式是直接在需要监控项目的入口文件引入，通常是在`public/index.php`中添加：
	
	````php
	require '/home/www/cai/php-monitor/src/autoPrepend.php';
    ````
    
	添加配置后的效果如下（除核心代码，其他代码都是为了演示说明）:
	
	````php
	<?php
	use pm\common\Router;
	
	//核心代码在此
	require '/home/www/cai/php-monitor/src/autoPrepend.php';
	
	include 'vendor/autoload.php';
	$config = require('src/config/config.php');
	(new Router($config))->run();
	````
	添加埋点之后，www.site.com 项目的请求记录可在 [http://127.0.0.1:8066](http://127.0.0.1:8066) 监控后台查看。
	
6. 更多细节

	* MongoDB的存储速度最快，如果对性能要求高，请优先使用它；
	* 修改配置文件的 profiler.enable 属性来修改采样频率，通常来说并不需要将所有请求都存储。比如 `rand(1, 100) > 60` 就是设置采样率为`40%`；
	* 修改配置文件的 profiler. filter_path 属性来过滤不想收集的服务，比如一些不关心执行效率的内网服务；
	
7. Swoole与Workerman支持
    
    Swoole和Workerman中无法使用`register_shutdown_function()`函数，需要手动运行`\pm\common\PMonitor::shutdown()`方法

    ````php
    public function onReceive(\swoole_server $serv, $fd, $from_id, $dataSrc)
    {
    require '/home/www/cai/php-monitor/src/autoPrepend.php';

    //your code

    \pm\common\PMonitor::shutdown($url,$serv->getClientInfo($fd,$from_id)['remote_ip'],'GET');
    }
    ````
	
## TODO
- [x] Sqlite存储方式开发；
- [x] 完善国际化；
- [x] 完善文档；
- [x] CI流程接入；
- [x] 补充单元测试；
- [x] Composer包封装；
- [ ] 重写xhprof扩展；
- [ ] 埋点模块与展示模块拆分；
- [ ] Docker接入；
	
## 反馈
提交ISSUE或者加我微信

![微信号城边编程](https://raw.githubusercontent.com/laynefyc/php-monitor/screenshot/screenshot/code-log.png)

[http://imgs.it2048.cn/code-log.png](http://imgs.it2048.cn/code-log.png)
