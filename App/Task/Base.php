<?php


namespace App\Task;

use EasySwoole\Task\AbstractInterface\TaskInterface;

abstract class Base implements TaskInterface
{
    protected $data;

    public function __construct($data)
    {
        // 保存投递过来的数据
        $this->data = $data;
    }

    public function onException(\Throwable $throwable, int $taskId, int $workerIndex)
    {
        // 异常处理
        throw $throwable;
    }

    abstract public function run(int $taskId, int $workerIndex);
}