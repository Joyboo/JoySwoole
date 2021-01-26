<?php


namespace EasySwoole\EasySwoole;


use App\Crontab\Central;
use EasySwoole\Component\Process\Manager;
use EasySwoole\Component\Timer;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Crontab\Crontab;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\ORM\Db\Connection;
use EasySwoole\ORM\DbManager;
use EasySwoole\RedisPool\RedisPool;
use EasySwoole\Redis\Config\RedisConfig;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');

        self::loadConfig();

        self::registerDb();

        self::registerRedis();
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

    /**
     * 注册消费进程
     */
    public static function registerWorker()
    {
        $worker = include_once EASYSWOOLE_ROOT . "/App/Common/config/worker.php";
        // 单个项目额外定义的worker
        $cfgWorker = Config::getInstance()->getConf('worker_list');
        if (is_array($cfgWorker)) {
            $worker = array_merge($worker, $cfgWorker);
        }

        foreach($worker as $key => $value) {
            $psnum = intval($value['psnum'] ?? 1);
            for ($i = 1; $i <= $psnum; ++$i) {
                $processConfig= new \EasySwoole\Component\Process\Config();
                $processConfig->setProcessName("worker_process_{$key}_{$i}");
                $processConfig->setArg($value);

                $class = "\\App\\Worker\\{$key}";
                if (!class_exists($class)) {
                    //todo excaption
                    var_dump($class . ' not found, woker');
                    return;
                }

                Manager::getInstance()->addProcess(new $class($processConfig));
            }
        }
    }

    /**
     * 注册redis连接池
     */
    public static function registerRedis()
    {
        $poolCfg = Config::getInstance()->getConf('redis_poll');
        $redisCfg = Config::getInstance()->getConf('redis');

        $redisConfig = new RedisConfig();
        if (isset($redisCfg['host'])) {
            $redisConfig->setHost($redisCfg['host']);
        }
        if (isset($redisCfg['port'])) {
            $redisConfig->setPort($redisCfg['port']);
        }
        if (isset($redisCfg['auth'])) {
            $redisConfig->setAuth($redisCfg['auth']);
        }
        if (isset($redisCfg['timeout'])) {
            $redisConfig->setTimeout($redisCfg['timeout']);
        }
        if (isset($redisCfg['db'])) {
            $redisConfig->setDb($redisCfg['db']);
        }
        if (isset($redisCfg['unixsock'])) {
            $redisConfig->setUnixSocket($redisCfg['unixsock']);
        }

        $redisPoolConfig = RedisPool::getInstance()->register($redisConfig);
        if (isset($poolCfg['min_num'])) {
            $redisPoolConfig->setMinObjectNum($poolCfg['min_num']);
        }
        if (isset($poolCfg['max_num'])) {
            $redisPoolConfig->setMaxObjectNum($poolCfg['max_num']);
        }
        if (isset($poolCfg['max_idle'])) {
            $redisPoolConfig->setMaxIdleTime($poolCfg['max_idle']);
        }
        // ...
    }

    /**
     * 注册orm连接池
     * @throws \EasySwoole\Pool\Exception\Exception
     */
    public static function registerDb()
    {
        $config = Config::getInstance()->getConf('mysql');
        $slow = intval(Config::getInstance()->getConf('mysql_slow_time'));

        if (empty($config)) {
            return;
        }

        foreach ($config as $key => $value) {
            $dbConfig = new \EasySwoole\ORM\Db\Config();
            $dbConfig->setDatabase($value['dbname']);
            $dbConfig->setUser($value['user'] ?? 'root');
            $dbConfig->setPassword($value['pwd'] ?? '');
            $dbConfig->setHost($value['host'] ?? '127.0.0.1');

            if (isset($value['port'])) {
                $dbConfig->setPort($value['port']);
            }
            if (isset($value['timeout'])) {
                $dbConfig->setTimeout($value['timeout']);
            }
            if (isset($value['charset'])) {
                $dbConfig->setCharset($value['charset']);
            }
            if (isset($value['auto_ping'])) {
                $dbConfig->setAutoPing($value['auto_ping']);
            }

            /************ 连接池配置************/

            // 设置获取连接池对象超时时间
            if (isset($value['pool_timeout'])) {
                $dbConfig->setGetObjectTimeout($value['pool_timeout']);
            }
            // 设置检测连接存活执行回收和创建的周期
            if (isset($value['pool_intval_check_time'])) {
                $dbConfig->setIntervalCheckTime($value['pool_intval_check_time']);
            }
            // 连接池对象最大闲置时间(秒)
            if (isset($value['pool_max_idle_time'])) {
                $dbConfig->setMaxIdleTime($value['pool_max_idle_time']);
            }
            // 设置最小连接池存在连接对象数量
            if (isset($value['pool_min_object_num'])) {
                $dbConfig->setMinObjectNum($value['pool_min_object_num']);
            }
            // 设置最大连接池存在连接对象数量
            if (isset($value['pool_max_object_num'])) {
                $dbConfig->setMaxObjectNum($value['pool_max_object_num']);
            }

            // 设置指定连接名称 后期可通过连接名称操作不同的数据库
            DbManager::getInstance()->addConnection(new Connection($dbConfig), $key);
        }
        /**
         * 注册全局Query回调
         * 如果不想使用全局性的onQuery, 可以在执行操作的时候调用onQuery方法, 以此来实现针对特定模型的回调
         * 实例:  User::create()->onQuery(function ($res, $builder, $start) {})->get(1);
         */
        DbManager::getInstance()->onQuery(function (\EasySwoole\ORM\Db\Result $res,\EasySwoole\Mysqli\QueryBuilder $builder,float $start) use ($slow) {
            $sql = $builder->getLastQuery();
            var_dump($sql);
            if (bcsub(time(), $start, 3) >= $slow) {
                // todo 记录慢日志
            }
        });
    }

    /**
     * 自定义加载配置
     * 多个项目运行时，Config目录下的配置优先级要高于系统配置
     * @author Joyboo
     * @date 2021-01-21
     */
    public static function loadConfig()
    {
        $config = Config::getInstance()->getConf();
        // 当前运行的项目
        $symbol = $config['symbol'];
        if (empty($symbol)) {
            return;
        }
        $filePath = EASYSWOOLE_ROOT . "/App/Common/config/{$symbol}.php";
//        Config::getInstance()->loadFile($filePath);
        if (!file_exists($filePath)) {
            return;
        }
        $confData = require_once $filePath;
        if (!is_array($confData)) {
            return;
        }
        $merge = array_merge_multi($config, $confData);
        Config::getInstance()->load($merge);
    }
}