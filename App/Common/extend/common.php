<?php

// 公共函数库

use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Core;
use EasySwoole\EasySwoole\Logger;

if (!function_exists('model')) {
    /**
     * 实例化模型
     * @param string $name
     * @return mixed
     * @throws Exception
     */
    function model($name = '')
    {
        $path = '\\App\\Models';
        $symbol = Config::getInstance()->getConf('symbol');
        $class = "{$path}\\{$symbol}\\{$name}";
        if (class_exists($class)) {
            return new $class();
        }
        $class = "{$path}\\{$name}";
        if (class_exists($class)) {
            return new $class;
        }
        throw new \Exception("model not found: {$name}");
    }
}

if (!function_exists('array_merge_multi')) {
    /**
     * 递归合并数组
     * @return array
     */
    function array_merge_multi()
    {
        $args = func_get_args();
        $array = array();
        foreach ($args as $arg) {
            if (is_array($arg)) {
                foreach ($arg as $k => $v) {
                    if (is_array($v)) {
                        $array[$k] = isset($array[$k]) ? $array[$k] : array();
                        $array[$k] = array_merge_multi($array[$k], $v);
                    } else {
                        $array[$k] = $v;
                    }
                }
            }
        }
        return $array;
    }
}

if (!function_exists('logger')) {
    /**
     * 返回Logger对象
     * @return Logger
     */
    function logger()
    {
        return Logger::getInstance();
    }
}

if (!function_exists('trace')) {
    /**
     * 写日志
     * @param string $msg
     * @param string $type
     */
    function trace($msg = '', $type = '')
    {
        if (is_array($msg)) {
            $msg = json_encode($msg, JSON_UNESCAPED_UNICODE);
        }
        logger()->info($msg, $type);
    }
}

if (!function_exists('runEnv')) {
    /**
     * 运行环境
     * @return int
     */
    function runEnv()
    {
        switch (Core::getInstance()->runMode()) {
            case 'dev': // 开发
                return 2;
            case 'protest': // todo 待定,外网测试服
                return 1;
            case 'produce': // 生产
                return 0;
        }
    }
}

if (!function_exists('runEnvDev')) {
    /**
     * 是否开发环境
     * @return bool
     */
    function runEnvDev()
    {
        return runEnv() === 2;
    }
}

if (!function_exists('config')) {
    /**
     * 获取/设置配置
     * @param $name
     * @param null $value
     * @return array|bool|mixed|null
     */
    function config($name = '', $value = null)
    {
        $config = Config::getInstance();
        if (is_null($value)) {
            return $config->getConf($name);
        }
        return $config->setConf($name, $value);
    }
}