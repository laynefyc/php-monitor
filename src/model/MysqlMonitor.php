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

    public function getList($dto){
        return $this->getListCommon($dto,'mysql');
    }


}