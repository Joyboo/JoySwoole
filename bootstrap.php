<?php
//全局bootstrap事件
date_default_timezone_set('Asia/Shanghai');

//ini_set('memory_limit', ((1 << 10) . 'M');

// 注册自定义命令
\EasySwoole\Command\CommandManager::getInstance()->addCommand(new \App\Command\InitDb());