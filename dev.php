<?php
$_config = [
    'SERVER_NAME' => "easyswoole",
    'MAIN_SERVER' => [
        'LISTEN_ADDRESS' => '0.0.0.0',
        'PORT' => 9501,
        'SERVER_TYPE' => EASYSWOOLE_WEB_SERVER, //可选为 EASYSWOOLE_SERVER  EASYSWOOLE_WEB_SERVER EASYSWOOLE_WEB_SOCKET_SERVER
        'SOCK_TYPE' => SWOOLE_TCP,
        'RUN_MODEL' => SWOOLE_PROCESS,
        'SETTING' => [
            'worker_num' => 8,
            'reload_async' => true,
            'max_wait_time'=>3
        ],
        'TASK'=>[
            'workerNum'=>4,
            'maxRunningNum'=>128,
            'timeout'=>15
        ]
    ],
    // .sock连接目录
    'TEMP_DIR' => '/tmp/easyswoole',
    'LOG_DIR' => null,

    // 项目标识
    'symbol' => 'joyboo',

    'mysql' => [
        'new_central' => [
            'dbname' => 'new_central',
//            'dbname' => 'joyboo',
            'host' => '127.0.0.1',
            'port' => 3306,
            'pwd' => '0987abc123'
        ]
    ],
    // 慢日志阀值，秒
    'mysql_slow_time' => 3,
    // redis连接池参数
    'redis_poll' => [],
    // 日志目录格式
    'logger_dir_format' => 'Ym',
];

// model命名空间
$_config['model_namespace'] = [
    '\\App\\Models\\' . $_config['symbol'],
    '\\App\\Models',
];

return $_config;
