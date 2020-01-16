<?php
namespace pm\model\common;
use pm\common\Profile;

trait TraitModel {

    public function insertData($data){
        $this->setRawAttributes($data,true);
        $this->save();
    }
    
    public function getListCommon($dto,$driver){

        if(!empty($dto['field']) && !empty($dto['order'])){
            $db = self::orderBy($dto['field'], $dto['order']);
        }else{
            if($driver==='mongodb')
            {
                $db = self::orderBy('created_at','desc');
            }else{
                $db = self::orderBy('id','desc');
            }
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
        
        $pid = $driver==='sqlite'?'rowid as id':'id';
        $rtn = $db->paginate(intval($dto['pageSize']),[
            $pid,'url','server_name','pmu','wt','cpu',
            'ct','mu','request_time','type','ip'
        ],'page', $dto['current'])->toArray();
        if($driver==='mongodb'){
            foreach($rtn['data'] as $k=>$v){
                $rtn['data'][$k]['id'] = $v['_id'];
                unset($rtn['data'][$k]['_id']);
            }
        }
        return $rtn;
    }


    public function findOne($dto){

        $rtn = [
            'server' => [],
            'funcList' => [],
            'flameGraph' => [],
            'sql'=>[]
        ];
        if(!empty($dto['id'])){
            $model = self::find($dto['id']);
            $server = json_decode($model->server,true);
            $sArr = [];
            $i = 0;
            $j = 0;
            foreach($server as $k=>$val){
                if($j==0){
                    $sArr[$i]['a'] = $k;
                    $sArr[$i]['b'] = $val;
                    $j++;
                }else{
                    $sArr[$i]['c'] = $k;
                    $sArr[$i]['d'] = $val;
                    $j=0;
                    $i++;
                }
            }
            $rtn['server'] = $sArr;
            $list = json_decode($model->profile,true);
            $rtn['sql'] = isset($list['sql'])?$list['sql']:[];
            $show = new Profile(isset($list['profile'])?$list['profile']:[]);
            $rtn['funcList'] = $show->getProfileBySort();
        }
        return $rtn;
    }

    public function findFlame($dto){

        $rtn = [
            'wt' => [],
            'mu' => []
        ];
        if(!empty($dto['id'])){
            $model = self::find($dto['id']);
            $list = json_decode($model->profile,true);
            $show = new Profile(isset($list['profile'])?$list['profile']:[]);
            $rtn['wt'] = $show->getFlamegraph('wt',0)['data'];
            $rtn['mu'] = $show->getFlamegraph('mu',0)['data'];
        }
        return $rtn;
    }

    public function findByUrl($dto){
        $db = self::select('server_name','request_time', 'wt','mu','ip');
        if(!empty($dto['request_time']) && is_array($dto['request_time'])){
            $db->whereBetween('request_time',$dto['request_time']);
        }
        if(!empty($dto['url'])){
            $db->where('url',$dto['url']);
        }
        return $db->get()->toArray();
    }
}