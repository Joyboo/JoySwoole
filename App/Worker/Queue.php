<?php


namespace App\Worker;

use App\Queue\RedisQueue;
use EasySwoole\Queue\Job;

class Queue extends Base
{
    public function run($arg)
    {
        go(function (){
            RedisQueue::getInstance()->consumer()->listen(function (Job $job) {
//                $pool = $this->getDb();
                var_dump("消费了队列: ");
                var_dump($job->getJobData());
            });
        });
    }
}