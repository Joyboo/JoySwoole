<?php


namespace App\Task;

/**
 * 异步任务
 * Class Central
 * @package App\Task
 */
class Central extends Base
{
    public function run(int $taskId, int $workerIndex)
    {
        // 执行逻辑
        var_dump('this is sync task', $this->data);
    }
}