<?php


namespace App\Worker;

use App\Queue\RedisQueue;
use EasySwoole\Queue\Job;

class Queue extends Base
{
    public function run($arg)
    {
        go(function (){
            $Consumer = RedisQueue::getInstance()->consumer();
            $Consumer->listen(function (Job $job) use ($Consumer) {
//                $pool = $this->getDb();
                var_dump("消费了队列: ");
                var_dump($job->getJobData());

                // 根据入库状态确认任务
                $Consumer->confirm($job);
            });
        });
    }
}