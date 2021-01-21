<?php


namespace App\Worker;

use EasySwoole\Component\Process\AbstractProcess;

/**
 * 消费消息队列
 * Class Base
 * @package App\Worker
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
        $this->addTick(500, function (){
            if (!$this->isRun) {
                $this->isRun = true;
                $redis = new \redis();// todo 此处为伪代码，请自己建立连接或者维护redis连接
                while (true){
                    try{
                        $task = $redis->lPop('task_list');
                        if (!$task) {
                            break;
                        }
                        // todo 异常处理
                        $this->exec($task);
                    } catch (\Throwable $throwable){
                        break;
                    }
                }
                $this->isRun = false;
            }
            var_dump($this->getProcessName().' task run check');
        });
    }

    public function onShutDown()
    {
    }

    public function onReceive(string $str, ...$args)
    {
    }

    /**
     * 子类执行逻辑
     * @return mixed
     */
    abstract protected function exec($task);
}