<?php
use PHPUnit\Framework\TestCase;
use pm\common\PMonitor;
use pm\model\SqliteMonitor;
class PMonitorTest extends TestCase
{
    public function testMonitor()
    {
		$config = require(dirname(__DIR__).'/src/config/config.php');
		PMonitor::loadConfig($config);
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$_SERVER['HTTP_CACHE_CONTROL'] = 'no-cache';
		$_SERVER['SERVER_NAME'] = '127.0.0.1';
		$this->assertTrue(PMonitor::shutdown());
	}
	
	public function testApi(){
		$db = new SqliteMonitor();
		$rtn = $db->getList(['pageSize'=>1,'current'=>1]);
		$id = $rtn['data'][0]['id'];
		$this->assertNotEmpty($rtn['data'][0]);

		$rtn = $db->findOne($id);
		$this->assertTrue(isset($rtn['server']));

		$rtn = $db->findFlame($id);
		$this->assertTrue(isset($rtn['wt']));

		$rtn = $db->findByUrl(['url'=>'phpunit ']);
		$this->assertTrue(count($rtn)>0);
	}
}