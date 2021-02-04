<?php

return [
    'install' => [
        'remark' => '用户注册',
        'psnum' => 2, // 启动进程数量
        'redis' => [
            'queue' => 'report:install', // redis队列
            'db' => 9,
        ],
        'type' => 'redis', // type|process
        'param_list' => 'instime|account|channel|sid|regsid|isnew|pfid|logintime|os|bs'
    ]
];