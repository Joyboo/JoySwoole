<?php


namespace App\Crontab;

use EasySwoole\EasySwoole\Crontab\AbstractCronTask;


/**
 * 定时任务，最小粒度为分钟，如需搞定度定时器请注册Timer
 *
 * Command:
 *   php easyswoole crontab -h  查看帮助
 *   php easyswoole crontab show  查看所有注册的任务
 *   php easyswoole crontab stop --name=TASK_NAME 停止指定的Crontab
 *   php easyswoole crontab resume --name=TASK_NAME  恢复指定的Crontab
 *   php easyswoole crontab run --name=TASK_NAME  立即跑一次指定的Crontab
 *
 * Class Central
 * @package App\Crontab
 */
class Central extends AbstractCronTask
{
    public static function getRule():string
    {
        return '*/1 * * * *';
    }
    public static function getTaskName():string
    {
        return 'Central_exp';
    }

    function run(int $taskId,int $workerIndex)
    {
        var_dump(date('Y-m-d H:i:s'));
        // 可投递给task异步处理
//        TaskManager::getInstance()->async(function (){});
    }

    public function onException(\Throwable $throwable, int $taskId, int $workerIndex)
    {
        throw $throwable;
    }
}