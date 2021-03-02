<?php


namespace App\Queue;

use EasySwoole\Component\Singleton;
use EasySwoole\Queue\Queue;

class RedisQueue extends Queue
{
    use Singleton;
}