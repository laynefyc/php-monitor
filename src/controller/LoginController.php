<?php
namespace pm\controller;

use pm\common\Controller;

class LoginController extends Controller{
    public function accountAction(){
        $post = json_decode(file_get_contents('php://input', 'r'),true);

        $userName = isset($post['userName'])?$post['userName']:'';
        $password = isset($post['password'])?$post['password']:'';
        $rtn = [
            "status" => "error",
            "type" => "account",
            "currentAuthority" => "guest"
        ];
        if(!empty($userName)&&!empty($password)){
            foreach($this->config['user'] as $val){
                if($val['account'] === $userName && $val['password'] === $password ){
                    $rtn = [
                        "status" => "ok",
                        "type" => "account",
                        "currentAuthority" => "admin"
                    ];
                    session_start(); 
                    $_SESSION['ACCOUNT'] = [
                        'name' => $userName,
                        'time' => time()
                    ];
                    break;
                }
            }
        }
        return $this->response($rtn);
    }

    public function outAction(){
        session_start();
        unset($_SESSION['ACCOUNT']);
    }
}