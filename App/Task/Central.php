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
        $data = $this->data;
        if (!is_array($data)) {
            logger()->error(__METHOD__ . "仅支持数组传参, data:" . var_export($data, true), 'error');
            return;
        }

        $className = "\\App\\Models\\" . ucfirst($data['class'] ?? 'Crontab');
        $method = $data['method'] ?? 'index';

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

            unset($data['class'], $data['method']);
            (new $className())->$method($data);
        } catch (\Exception | \Throwable $e) {
            logger()->error($e->getMessage(), 'error');
            return $e->getMessage();
        }
        return "执行完成!! $className -> $method";
    }
}