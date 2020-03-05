<?php
namespace pm\common;
class PMonitor{

    private static $extensionName = "";
    private static $dbServer = [];
    private static $filter = false;

    public  static function loadConfig(array $config)
    {
        if($config['debug']){
            ini_set('display_errors',1);
        }
        //filter request
        if(!$config['profiler']['enable']
         || in_array($_SERVER['DOCUMENT_ROOT'],$config['profiler']['filter_path'])){
            self::$filter = true;
            return true;
        }

        if (!isset($_SERVER['REQUEST_TIME_FLOAT'])) {
            $_SERVER['REQUEST_TIME_FLOAT'] = microtime(true);
        }

        if(extension_loaded($config['extension'])){
            self::$extensionName = $config['extension'];
        }else{
            throw new \Exception(sprintf('Cannot find module (%s)',$config['extension']));
        }

        if (self::$extensionName == 'uprofiler') {
            uprofiler_enable(UPROFILER_FLAGS_CPU | UPROFILER_FLAGS_MEMORY);
        } else if (self::$extensionName == 'tideways_xhprof') {
            tideways_xhprof_enable(TIDEWAYS_XHPROF_FLAGS_MEMORY | TIDEWAYS_XHPROF_FLAGS_MEMORY_MU | TIDEWAYS_XHPROF_FLAGS_MEMORY_PMU | TIDEWAYS_XHPROF_FLAGS_CPU);
        } else if (self::$extensionName == 'tideways') {
            tideways_enable(TIDEWAYS_FLAGS_CPU | TIDEWAYS_FLAGS_MEMORY);
            tideways_span_create('sql');
        } else if(function_exists('xhprof_enable')){
            xhprof_enable(XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY);
        }else{
            throw new \Exception("Please check the extension name in config/config.default.php \r\n,you can use the 'php -m' command.", 1);
        }
        self::$dbServer = $config['save'];
    }

    public static function router(){
        $r = isset($_GET['r'])&&!empty($_GET['r'])?explode('/',$_GET['r']):[];
        if(!isset($r[1])){
            throw new \Exception("URL error, like index.php?r=api/test.", 1);
        }else{
            if(preg_match('/^[a-zA-Z]{1,32}$/U',$r[0]) === 1){
                $controller = '\pm\controller\\'.$r[0].'Controller';
            }else{
                throw new \Exception("Controller names can only contain letters.", 1);
            }

            if(preg_match('/^[a-zA-Z-]{0,32}$/U',$r[1]) === 1){

                $actionName = preg_replace_callback('/([-]+([a-z]{1}))/i',function($matches){
                    return strtoupper($matches[2]);
                },$r[1]);
                $action = $actionName.'Action';
            }else{
                throw new \Exception("Action names can only contain letters and '-'.", 1);
            }
            if(class_exists($controller)){
                $myclass = new $controller();
                if(method_exists($myclass,$action)){
                    $myclass->$action();
                }else{
                    throw new \Exception("Action not found.", 1);
                }
            }else{
                throw new \Exception("Controller not found.", 1);
            }
        }
    }

    public static function shutdown($action='',$ip='',$requestType=''){
        if(self::$filter) return true;
        $extension = self::$extensionName;
        if ($extension == 'uprofiler') {
            $data['profile'] = uprofiler_disable();
        } else if ($extension == 'tideways_xhprof') {
            $data['profile'] = tideways_xhprof_disable();
        } else if ($extension == 'tideways') {
            $data['profile'] = tideways_disable();
            $sqlData = tideways_get_spans();
            $data['sql'] = array();
            if(isset($sqlData[1])){
                foreach($sqlData as $val){
                    if(isset($val['n'])&&$val['n'] === 'sql'&&isset($val['a'])&&isset($val['a']['sql'])){
                        $_time_tmp = (isset($val['b'][0])&&isset($val['e'][0]))?($val['e'][0]-$val['b'][0]):0;
                        if(!empty($val['a']['sql'])){
                            $data['sql'][] = [
                                'time' => $_time_tmp,
                                'sql' => $val['a']['sql']
                            ];
                        }
                    }
                }
            }
        } else if(function_exists('xhprof_enable')){
            $data['profile'] = xhprof_disable();
        }else{
            throw new \Exception("Please check the extension name in config/config.default.php \r\n,you can use the 'php -m' command.", 1);
        }
        // ignore_user_abort(true) allows your PHP script to continue executing, even if the user has terminated their request.
        // Further Reading: http://blog.preinheimer.com/index.php?/archives/248-When-does-a-user-abort.html
        // flush() asks PHP to send any data remaining in the output buffers. This is normally done when the script completes, but
        // since we're delaying that a bit by dealing with the xhprof stuff, we'll do it now to avoid making the user wait.
        ignore_user_abort(true);
        flush();
        $uri = array_key_exists('REQUEST_URI', $_SERVER)
            ? $_SERVER['REQUEST_URI']
            : null;
        if (empty($uri) && isset($_SERVER['argv'])) {
            $cmd = basename($_SERVER['argv'][0]);
            $uri = $cmd . ' ' . implode(' ', array_slice($_SERVER['argv'], 1));
        }
        $requestTimeFloat =  explode(' ',microtime());
        $requestTsMicro = array('sec' => $requestTimeFloat[1], 'usec' => $requestTimeFloat[0]*1000000);
        if(!empty($requestType)){$_SERVER['REQUEST_METHOD'] = $requestType;}
        if(!empty($ip)){$_SERVER['REMOTE_ADDR'] = $ip;}

        $data['meta'] = array(
            'url' => empty($action)?$uri:$action,
            'SERVER' => $_SERVER,
            'get' => $_GET,
            'env' => $_ENV,
            'request_ts_micro' => $requestTsMicro
        );

        $obj = new Format($data,self::$dbServer);
        return $obj->save();
    }
}
