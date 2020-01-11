<?php
namespace pm\model\common;
use pm\common\Profile;

trait TraitModel {
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