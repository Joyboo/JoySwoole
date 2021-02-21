<?php


namespace EasySwoole\EasySwoole;

use App\Hander\LogHander;
use App\Hander\TriggerHander;
use App\WebSocket\WebSocketEvents;
use App\WebSocket\WebSocketParser;
use App\WeChat\WeChatManager;
use EasySwoole\Component\Process\Exception;
use EasySwoole\Component\Process\Manager;
use EasySwoole\Component\Timer;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Crontab\Crontab;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\FastCache\Cache;
use EasySwoole\ORM\Db\Connection;
use EasySwoole\ORM\DbManager;
use EasySwoole\RedisPool\RedisPool;
use EasySwoole\Redis\Config\RedisConfig;
use EasySwoole\ORM\Db\Result;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\WeChat\WeChat;
use EasySwoole\Socket\Dispatcher;
use Swoole\Websocket\Server as WSserver;
use Swoole\WebSocket\Frame;
use EasySwoole\Component\Di;
use EasySwoole\EasySwoole\SysConst;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\Http\Message\Status;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');

        // 注册自定义日志处理器
        Logger::getInstance(new LogHander());

        // 注册自定义异常处理器
        Trigger::getInstance(new TriggerHander());

        // 加载自定义配置
        self::loadConfig();

        // 注册mysql连接池
        self::registerDb();

        // 注册redis连接池
        self::registerRedis();

        // 注册全局onRequest,支持跨域
        Di::getInstance()->set(SysConst::HTTP_GLOBAL_ON_REQUEST, function (Request $request, Response $response){
            $response->withHeader('Access-Control-Allow-Origin', '*');
            $response->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
            $response->withHeader('Access-Control-Allow-Credentials', 'true');
            $response->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
            if ($request->getMethod() === 'OPTIONS') {
                $response->withStatus(Status::CODE_OK);
                return false;
            }
            $response->withHeader('Content-type', 'application/json;charset=utf-8');
            return true;
        });
    }

    public static function mainServerCreate(EventRegister $register)
    {
        /*********** 处理ws事件 *********/
        $register->set(EventRegister::onManagerStart, function (\Swoole\Server $server) {
            self::setProcessName(config('SERVER_NAME') . '.Manager');
        });
        // 创建一个 Dispatcher 配置
        $conf = new \EasySwoole\Socket\Config();
        // 设置 Dispatcher 为 WebSocket 模式
        $conf->setType(\EasySwoole\Socket\Config::WEB_SOCKET);
        // 设置解析器对象
        $conf->setParser(new WebSocketParser());
        // 创建 Dispatcher 对象 并注入 config 对象
        $dispatch = new Dispatcher($conf);
        // 给server 注册相关事件 在 WebSocket 模式下  on message 事件必须注册 并且交给 Dispatcher 对象处理
        $register->set(EventRegister::onMessage, function (WSserver $server, Frame $frame) use ($dispatch) {
            $dispatch->dispatch($server, $frame->data, $frame);
        });
        // 注册服务事件
        $register->add(EventRegister::onOpen, [WebSocketEvents::class, 'onOpen']);
        $register->add(EventRegister::onClose, [WebSocketEvents::class, 'onClose']);
        $register->add(EventRegister::onWorkerError, [WebSocketEvents::class, 'onError']);



        self::registerWeChat();

        // 注册消费进程
        self::registerWorker();

        self::registerCrontab();

        // 注册Timer,高精度定时器，原型是swoole_timer_tick
        /*Timer::getInstance()->loop(10 * 1000, function () {
            echo "this timer runs at intervals of 10 seconds\n";
        });*/

        // fast-cache
        $fastConfig = new \EasySwoole\FastCache\Config(config('fast_cache') ?? []);
        Cache::getInstance($fastConfig)->attachToServer(ServerManager::getInstance()->getSwooleServer());
    }

    /**
     * 注册消费进程
     */
    public static function registerWorker()
    {
        $worker = include_once EASYSWOOLE_ROOT . "/App/Common/config/worker.php";
        // 单个项目额外定义的worker
        $cfgWorker = config('worker_list');
        if (is_array($cfgWorker)) {
            $worker = array_merge($worker, $cfgWorker);
        }

        $proName = config('SERVER_NAME');
        foreach ($worker as $key => $value) {
            $key = ucfirst($key);

            $proCfg = [
                'processName' => $proName . '.' . $key,
                'processGroup' => 'report',
                'arg' => $value, // 传递参数到自定义进程中
                'enableCoroutine' => true, // 设置 自定义进程自动开启协程环境
            ];

            $class = $value['class'] ?? "\\App\\Worker\\{$key}";
            if (!class_exists($class)) {
                throw new Exception($class . ' worker not found');
            }

            switch ($value['type']) {
                case "process":
                    $processConfig = new \EasySwoole\Component\Process\Config($proCfg);
                    $customProcess = new $class($processConfig);
                    // 注入DI
                    Di::getInstance()->set($key, $customProcess->getProcess());
                    Manager::getInstance()->addProcess($customProcess);
                    break;
                case "redis":
                    $psnum = intval($value['psnum'] ?? 1);
                    for ($i = 1; $i <= $psnum; ++$i) {
                        $proCfg['processName'] = $key . '_' . $i;
                        $processConfig = new \EasySwoole\Component\Process\Config($proCfg);
                        Manager::getInstance()->addProcess(new $class($processConfig));
                    }
                    break;
                default:
                    throw new Exception('自定义进程type error: ' . $value['type']);
            }
        }
    }

    /**
     * 注册redis连接池
     */
    public static function registerRedis()
    {
        $config = config('redis') ?? [];
        $redisConfig = new RedisConfig($config);
        RedisPool::getInstance()->register($redisConfig);
    }

    /**
     * 注册orm连接池
     * @throws \EasySwoole\Pool\Exception\Exception
     */
    public static function registerDb()
    {
        $config = config('mysql') ?? [];
        $slow = intval(config('mysql_slow_time'));

        if (empty($config)) {
            return;
        }

        foreach ($config as $key => $value) {
            $dbConfig = new \EasySwoole\ORM\Db\Config($value);

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
        DbManager::getInstance()->onQuery(
            function (Result $res, QueryBuilder $builder,float $start) use ($slow) {
                $sql = $builder->getLastQuery();
                if (runEnvDev()) {
                    logger()->info($sql, 'sql');
                }
                if ($slow && bcsub(time(), $start, 3) >= $slow) {
                    // 慢日志
                    logger()->waring($sql, 'sql_long');
                }
            }
        );
    }

    /**
     * 自定义加载配置
     * 多个项目运行时，Config目录下的配置优先级要高于系统配置
     * @author Joyboo
     * @date 2021-01-21
     */
    public static function loadConfig()
    {
        $config = config();
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


    public static function registerCrontab ()
    {
        $crontab = config('crontab');
        if (empty($crontab) || !is_array($crontab)) {
            return;
        }
        foreach ($crontab as $cron) {
            Crontab::getInstance()->addTask($cron);
        }
    }

    public static function setProcessName($processName = '')
    {
        if (!in_array(PHP_OS, ['Darwin', 'CYGWIN', 'WINNT']) && !empty($processName)) {
            cli_set_process_title($processName);
        }
    }

    public static function registerWeChat()
    {
        $weChatConfig = new \EasySwoole\WeChat\Config();
        $weChatConfig->setTempDir(config('LOG_DIR') . '/wechat/');

        $weChatConfig->officialAccount()->setAppId(config('wechat.appId'));
        $weChatConfig->officialAccount()->setAppSecret(config('wechat.appSecret'));
        $weChatConfig->officialAccount()->setToken(config('wechat.token'));

        $weChat = new WeChat($weChatConfig);
        WeChatManager::getInstance()->register('default', $weChat);
    }
}