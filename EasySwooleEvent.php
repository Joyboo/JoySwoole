<?php


namespace EasySwoole\EasySwoole;


use App\Crontab\Central;
use EasySwoole\Component\Process\Manager;
use EasySwoole\Component\Timer;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Crontab\Crontab;
use EasySwoole\EasySwoole\Swoole\EventRegister;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');

        self::loadConfig();
    }

    public static function mainServerCreate(EventRegister $register)
    {
        // todo 定义全局自动注册

        // 注册消费进程
        self::registerWorker();

        // 注册Crontab
        Crontab::getInstance()->addTask(Central::class);

        // 注册Timer,高精度定时器，原型是swoole_timer_tick
        /*Timer::getInstance()->loop(10 * 1000, function () {
            echo "this timer runs at intervals of 10 seconds\n";
        });*/
    }

    public static function registerWorker()
    {
        // todo 读worker
        $worker = [];
        foreach($worker as $item) {
            $processConfig= new \EasySwoole\Component\Process\Config();
            $processConfig->setProcessName('worker_process_'.$item);

            $class = "\\App\\Worker\\{$item}";
            if (!class_exists($class)) {
                //todo excaption
                return;
            }

            Manager::getInstance()->addProcess(new $class($processConfig));
        }
    }

    /**
     * 自定义加载配置
     * 多个项目运行时，Config目录下的配置优先级要高于系统配置
     * @author Joyboo
     * @date 2021-01-21
     */
    public static function loadConfig()
    {
        // 当前运行的项目
        $symbol = $conf = Config::getInstance()->getConf('symbol');
        if (empty($symbol)) {
            return;
        }
        Config::getInstance()->loadFile(EASYSWOOLE_ROOT . "/App/Common/config/{$symbol}.php");
    }
}