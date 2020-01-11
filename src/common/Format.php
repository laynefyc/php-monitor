<?php
namespace pm\common;
use Illuminate\Database\Capsule\Manager as Capsule; 
use pm\model\MysqlMonitor;
use pm\model\MongoMonitor;
use pm\model\FileMonitor;
class Format{

    protected $_profile = [];
    protected $_sql = [];
    protected $_meta = [];
    protected $_dbName = "";

    public function __construct($data,$db)
    {
        $this->_dbName = strtolower($db['driver']);
        $this->initDb($db);
        $this->_sql = isset($data['sql'])?$data['sql']:[];
        $this->_meta = isset($data['meta'])?$data['meta']:[];
        $this->_profile = isset($data['profile'])?$data['profile']:[];
    }

    public function initDb($db){
        //init ORM
        if($this->_dbName === 'file'){
            FileMonitor::$_file = $db['database'];
        }else{
            $capsule = new Capsule();
            if($this->_dbName === 'mongodb')
            {
                $capsule->getDatabaseManager()->extend('mongodb', function($config, $name)
                {
                    $config['name'] = $name;
                    return new \Jenssegers\Mongodb\Connection($config);
                });
            }
            $capsule->addConnection($db);
            $capsule->setAsGlobal();
            $capsule->bootEloquent();
        }
    }

    public function save(){
        $saveData = [
            'url' => $this->_meta['url'],
            'server_name' => (isset($this->_meta['SERVER'])&&isset($this->_meta['SERVER']['SERVER_NAME']))?$this->_meta['SERVER']['SERVER_NAME']:"",
            'get' => json_encode($this->_meta['get']),
            'server' => json_encode(isset($this->_meta['SERVER'])?$this->_meta['SERVER']:[]),
            'type' => isset($this->_meta['SERVER'])&&isset($this->_meta['SERVER']['REQUEST_METHOD'])? $this->_meta['SERVER']['REQUEST_METHOD'] : "",
            'ip' => isset($this->_meta['SERVER'])&&isset($this->_meta['SERVER']['REMOTE_ADDR'])? $this->_meta['SERVER']['REMOTE_ADDR'] : "",
            'request_time' => $this->_meta['request_ts_micro']['sec'],
            'request_time_micro' => $this->_meta['request_ts_micro']['usec'],
            'profile' => json_encode(["profile"=>$this->_profile,"sql"=>$this->_sql]),
            'mu' => $this->_profile['main()']['mu'],
            'pmu' => $this->_profile['main()']['pmu'],
            'ct' => $this->_profile['main()']['ct'],
            'cpu' => $this->_profile['main()']['cpu'],
            'wt' => $this->_profile['main()']['wt'],
        ];
        if($this->_dbName === 'mongodb')
        {
            return (new MongoMonitor())->insertData($saveData);
        }elseif ( $this->_dbName === 'mysql' ) {
            return (new MysqlMonitor())->insertData($saveData);
        }elseif ( $this->_dbName === 'file' ) {
            return (new FileMonitor())->insertData($saveData);
        }else{
            return false;
        }
    }

}