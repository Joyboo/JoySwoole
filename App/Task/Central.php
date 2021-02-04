<?php


namespace App\Task;

use EasySwoole\Task\AbstractInterface\TaskInterface;

/**
 * 异步任务
 * Class Central
 * @package App\Task
 */
class Central implements TaskInterface
{
    protected $data;

    public function __construct($data)
    {
        // 保存投递过来的数据
        $this->data = $data;
    }

    public function run(int $taskId, int $workerIndex)
    {
        // 执行逻辑
        var_dump('this is sync task', $this->data);
    }

    public function onException(\Throwable $throwable, int $taskId, int $workerIndex)
    {
        // 异常处理
        throw $throwable;
    }
}