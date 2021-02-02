<?php


namespace App\Crontab;


class Central extends Base
{
    public static function getRule():string
    {
        return '*/1 * * * *';
    }

    function run(int $taskId,int $workerIndex)
    {
        var_dump(date('Y-m-d H:i:s'));
        // 可投递给task异步处理
//        TaskManager::getInstance()->async(function (){});
    }
}