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
        $className = "\\App\\Models\\" . ucfirst($this->data['class'] ?? 'Crontab');
        $method = $this->data['method'] ?? 'index';

        // 参数可控，暂时不需要使用反射
//        $ref = new \ReflectionClass($className);
//        $public = $ref->getMethods(\ReflectionMethod::IS_PUBLIC);

        try {
            if (!class_exists($className)) {
                logger()->error("$className Not Found!", 'error');
                return;
            }

            if (!method_exists($className, $method)) {
                logger()->error("$method Not Found!", 'error');
                return;
            }

            (new $className())->$method();
        } catch (\Exception | \Throwable $e) {
            logger()->error($e->getMessage(), 'error');
            return $e->getMessage();
        }
        return "执行完成!! " . __METHOD__;
    }
}