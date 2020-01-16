-- sqlite
CREATE TABLE `php_monitor` (
`url` text  ,
`server_name` TEXT  DEFAULT NULL ,
`get` text ,
`pmu` INTEGER  DEFAULT NULL ,
`wt` INTEGER  DEFAULT NULL ,
`cpu` INTEGER  DEFAULT NULL ,
`ct` INTEGER NOT NULL ,
`mu` INTEGER  DEFAULT NULL ,
`request_time` INTEGER  NOT NULL DEFAULT '0' ,
`request_time_micro` INTEGER  DEFAULT '0' ,
`profile` longblob NOT NULL ,
`server` longblob ,
`type` TEXT DEFAULT NULL ,
`ip` TEXT DEFAULT NULL 
);

CREATE INDEX `php_monitor_idx_url` ON `php_monitor` (`url`);
CREATE INDEX `php_monitor_idx_ip` ON `php_monitor` (`ip`);


-- mysql
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