<?php
namespace pm\model;

use pm\model\common\iModelInterface;
use pm\model\common\TraitModel;

class MysqlMonitor extends \Illuminate\Database\Eloquent\Model implements iModelInterface {
    use TraitModel;
    protected $table = "php_monitor";
    protected $primaryKey = 'id';
    const CREATED_AT = null;
    const UPDATED_AT = null;

    public function insertData($data){
        $this->setRawAttributes($data,true);
        $this->save();
    }

    public function getList($dto){

        if(!empty($dto['field']) && !empty($dto['order'])){
            $db = self::orderBy($dto['field'], $dto['order']);
        }else{
            $db = self::orderBy('id','desc');
        }

        if(!empty($dto['type'])){
            $db->where('type',$dto['type']);
        }
        if(!empty($dto['url'])){
            $db->where('url',$dto['url']);
        }
        if(!empty($dto['server_name'])){
            $db->where('server_name',$dto['server_name']);
        }
        if(!empty($dto['request_time']) && is_array($dto['request_time'])){
            $db->whereBetween('request_time',$dto['request_time']);
        }
        if(!empty($dto['ip'])){
            $db->where('ip',$dto['ip']);
        }



        return $db->paginate($dto['pageSize'],[
            'id','url','server_name','pmu','wt','cpu',
            'ct','mu','request_time','type','ip'
        ],'page', $dto['current'])->toArray();
    }


}