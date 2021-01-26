<?php

// 公共函数库

use EasySwoole\EasySwoole\Config;

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