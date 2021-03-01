<?php


namespace App\HttpController\Admin;

use App\HttpController\Base;
use App\Model\AuthModel;
use EasySwoole\Http\Message\Status;
use EasySwoole\Mysqli\QueryBuilder;


class AdminNode extends Base
{
    public function getAll()
    {
        //  做树形结构缓存
        $param = $this->request()->getRequestParam();
        if (empty($param['pid'])) {
            // header顶级菜单
            $param['pid'] = 0;
        }

        $model = new \App\Models\AdminNode();
        $data = $model->where([
            'pid' => $param['pid'],
            'isshow' => 1
        ])->order('sort', 'ASC')->all(function (QueryBuilder $queryBuilder) use ($param) {
            if (isset($param['gid'])) {
                $queryBuilder->where("(isglb=1 or find_in_set(gids, '{$param['gid']}')<>0)");
            }
        });
        $this->writeJson(Status::CODE_OK, $data, 'success');
    }
}