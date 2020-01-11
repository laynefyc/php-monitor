<?php
namespace pm\controller;

use pm\common\Controller;

class ApiController extends Controller{

    private $account = "";

    public function __construct(...$args)
    {
        parent::__construct(...$args);
        $this->beforeAction();
    }
    public function beforeAction(){
        session_start();
        $sevenDay = 604800;
        if(isset($_SESSION['ACCOUNT'])
        && isset($_SESSION['ACCOUNT']['time'])
        && time() - $_SESSION['ACCOUNT']['time'] < $sevenDay
        ){
            $this->account = $_SESSION['ACCOUNT']['name'];
            session_write_close();
        }else{
            return $this->response([
                "status" => "error",
                "msg" => "login timeout"
            ]);
        }
    }
    public function getListAction(){
        
        $this->get("current",1);
        $this->get("pageSize",20);
        $this->get("type");
        $this->get("url");
        $this->get("server_name");
        $this->get("request_time","",function($param){
            $tmp = explode('~',$this->requestDTO[$param]);
            $this->requestDTO[$param] = [strtotime($tmp[0]),strtotime($tmp[1])];
        });
        $this->get("ip");
        $this->get("field","");
        $this->get("order","",function($param){
            $this->requestDTO[$param] = strpos($this->requestDTO[$param],'asc') === 0?'asc':'desc';
        });

        $model = $this->getDb();
        $rtn = $model->getList($this->requestDTO);
        return $this->response(
            [
                "data" => $rtn['data'],
                "current" => $rtn['current_page'],
                "pageSize" => intval($rtn['per_page']),
                "success" => true,
                "total" => $rtn['total']
            ]
        );
    }

    public function infoAction(){
        
        $this->get("id",0);
        $model = $this->getDb();
        $rtn = $model->findOne($this->requestDTO);
        return $this->response($rtn);
    }

    public function flameAction(){
        
        $this->get("id",0);
        $model = $this->getDb();
        $rtn = $model->findFlame($this->requestDTO);
        return $this->response($rtn);
    }

    public function getListByUrlAction(){
        
        $this->get("request_time","",function($param){
            $tmp = explode('~',$this->requestDTO[$param]);
            $this->requestDTO[$param] = [strtotime($tmp[0]),strtotime($tmp[1])];
        });
        $this->get("url");
        $model = $this->getDb();
        $rtn = $model->findByUrl($this->requestDTO);
        return $this->response(['list'=>$rtn]);
    }

    public function currentUserAction(){
        return $this->response([
            'country' => "China",
            'name' => $this->account
        ]);
    }
}