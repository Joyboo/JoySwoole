<?php

return [
    'redis' => [
//        'host' => '127.0.0.1',
//        'port' => 6379,
//        'auth' => '',
//        'timeout' => 3,
        'db' => 13
    ],
    'mysql' => [
        'central_log' => [
            'dbname' => 'new_qj_log',
            'host' => '127.0.0.1',
            'port' => 3306,
            'pwd' => '0987abc123'
        ],
        'central_cnt' => [
            'dbname' => 'new_qj_cnt',
            'host' => '127.0.0.1',
            'port' => 3306,
            'pwd' => '0987abc123'
        ],
    ],
];