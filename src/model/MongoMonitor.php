<?php
namespace pm\model;
use pm\model\common\iModelInterface;
use pm\model\common\TraitModel;

class MongoMonitor extends \Jenssegers\Mongodb\Eloquent\Model implements iModelInterface {
    use TraitModel;
    protected $collection = "php_monitor";
    public function getList($dto){
        return $this->getListCommon($dto,'mongodb');
    }

}