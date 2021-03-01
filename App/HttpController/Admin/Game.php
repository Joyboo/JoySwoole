<?php


namespace App\HttpController\Admin;

use App\HttpController\Base;
use Swoole\Http\Status;

class Game extends Base
{
    public function meunGameList()
    {
//        $param = $this->request()->getRequestParam();
        $model =  new \App\Models\Game();
        $data = $model->field('id,name,gname')->order('sort ', 'asc')->all();
        $this->writeJson(Status::OK, $data);
    }
}