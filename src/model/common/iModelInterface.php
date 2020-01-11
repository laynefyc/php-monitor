<?php
namespace pm\model\common;

interface iModelInterface{
    public function insertData($data);
    public function getList($dto);
    public function findOne($dto);
    public function findFlame($dto);
    public function findByUrl($dto);
}