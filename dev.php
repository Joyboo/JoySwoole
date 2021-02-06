<?php
$_config = [
    'SERVER_NAME' => "easyswoole",
    'MAIN_SERVER' => [
        'LISTEN_ADDRESS' => '0.0.0.0',
        'PORT' => 9501,
        'SERVER_TYPE' => EASYSWOOLE_WEB_SOCKET_SERVER, //可选为 EASYSWOOLE_SERVER  EASYSWOOLE_WEB_SERVER EASYSWOOLE_WEB_SOCKET_SERVER
        'SOCK_TYPE' => SWOOLE_TCP,
        'RUN_MODEL' => SWOOLE_PROCESS,
        'SETTING' => [
            'worker_num' => 3,
            'reload_async' => true,
            'max_wait_time'=>3
        ],
        'TASK'=>[
            'workerNum'=>2,
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
            'database' => 'new_central',
//            'database' => 'joyboo',
            'host' => '127.0.0.1',
            'port' => 3306,
            'user' => 'root',
            'password' => '0987abc123'
        ]
    ],

    'wechat' => [
        'appId' => get_cfg_var('env.wechat_app_id'),
        'appSecret' => get_cfg_var('env.wechat_app_secret'),
        'token' => get_cfg_var('env.wechat_token'),

        'templateId' => 'KvYPbmyMATKvpuxc4OornkFINZSTZ41vMqEjGq63svk', // 默认使用的模板id
        'url' => 'http://www.baidu.com', // 点击跳转到哪里去
        'touser' => 'oLtkP5y8bdhhu-h13l5M-3U6Rchs', // 默认接受的openid
        'err_limit_time' => 5, // 程序错误时，相同的内容多长时间内不重复发送，单位分钟
    ],
    // 是否需要做自动注册
    'crontab' => [
        \App\Crontab\Central::class
    ],
    // 慢日志阀值，秒
    'mysql_slow_time' => 3,
    // redis连接池参数
    'redis_poll' => [],
    // 日志目录格式
    'logger_dir_format' => 'Ym',

    'fast_cache' => [
        'tempDir' => '/tmp/easyswoole', // 同上面的TEMP_DIR目录
        'serverName' => 'easyswoole',
        'workerNum' => 2
    ]
];

// model命名空间
$_config['model_namespace'] = [
    '\\App\\Models\\' . $_config['symbol'],
    '\\App\\Models',
];

return $_config;
