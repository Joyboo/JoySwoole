<?php


namespace App\Crontab;


use Cron\CronExpression;
use EasySwoole\EasySwoole\Task\TaskManager;

/**
 * Class Central
 * @author Joyboo
 * @date 2021-02-04
 * @package App\Crontab
 */
class Central extends Base
{
    public static function getRule():string
    {
        return '* * * * *';
    }

    public function run(int $taskId,int $workerIndex)
    {
        // 获取执行Crontab列表
        $cron = model('Crontab')->getCrontab();
        if (empty($cron)) {
            return;
        }
        $task = TaskManager::getInstance();
        foreach ($cron as $value) {
            if (!CronExpression::isValidExpression($value['rule'])) {
                logger()->error("运行规则设置错误 " . json_encode($value->toArray(), JSON_UNESCAPED_UNICODE), 'error');
                continue;
            }
            $className = $value['class'];
            if (!class_exists($className)) {
                logger()->error("{$className} 不存在", 'error');
                continue;
            }
            $isDue = CronExpression::factory($value['rule'])->isDue();
            if (!$isDue) {
                // 时间未到
                continue;
            }
            // 尝试json_decode
            $args = json_decode($value['args'], true);

            $class = new $className(is_array($args) ? $args : $value['args']);
            // 投递给异步任务
            $task->async($class, function ($reply, $taskId, $workerIndex) use ($value) {
                logger()->info("task finish! {$value['taskname']}, reply={$reply}, taskid={$taskId}");
            });
        }
    }
}