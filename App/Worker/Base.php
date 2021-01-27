<?php


namespace App\Worker;

use EasySwoole\Component\Process\AbstractProcess;

/**
 * 消费消息队列
 * Class Base
 * @package App\Worker
 * @document 自定义进程： https://www.easyswoole.com/Components/Component/process.html
 */
abstract class Base extends AbstractProcess
{
    private $isRun = false;

    public function run($arg)
    {
        /*
         * 举例，消费redis中的队列数据
         * 定时500ms检测有没有任务，有的话就while死循环执行
         */
        $this->addTick(500, function () {
            if ($this->isRun) {
                return;
            }
            $param = $this->getArg();

            $this->isRun = true;
            $redis = $this->getRedis();
            if (isset($param['redis']['db'])) {
                $redis->select($param['redis']['db']);
            }
            while (true){
                try{
                    $task = $redis->lPop($param['redis']['queue']);
                    if (!$task) {
                        break;
                    }

                    $decode = json_decode($task, true);
                    $this->exec(is_array($decode) ? $decode : $task);
                } catch (\Throwable $throwable){
                    throw $throwable;
                }
            }
            $this->isRun = false;
        });
    }

    public function onShutDown()
    {
        $this->isRun = false;
    }

    public function onReceive(string $str, ...$args)
    {
    }

    protected function getRedis()
    {
        return \EasySwoole\RedisPool\RedisPool::defer();
    }

    protected function getDb($name = 'new_central')
    {
        return \EasySwoole\ORM\DbManager::getInstance()->getConnection($name)->defer();
    }

    /**
     * 子类执行逻辑
     * @return mixed
     */
    abstract protected function exec($task);
}