<?php

// 公共函数库

use App\WeChat\WeChatManager;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Core;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\Utility\File;

if (!function_exists('model')) {
    /**
     * 实例化模型
     * @param string $name
     * @return mixed
     * @throws Exception
     */
    function model($name = '')
    {
        $namespace = config('model_namespace');
        foreach ($namespace as $np) {
            $class = "{$np}\\{$name}";
            if (class_exists($class)) {
                return new $class();
            }
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

if (!function_exists('curl')) {
    /**
     * 发起curl请求
     * @param string $method
     * @param string $url
     * @param array|null $params
     * @return \EasySwoole\Curl\Response
     */
    function curl(string $method, string $url, array $params = null): \EasySwoole\Curl\Response
    {
        $request = new \EasySwoole\Curl\Request( $url );
        switch( strtoupper($method) ){
            case 'GET' :
                if( $params && isset( $params['query'] ) ){
                    foreach( $params['query'] as $key => $value ){
                        $request->addGet( new \EasySwoole\Curl\Field( $key, $value ) );
                    }
                }
                break;
            case 'POST' :
                if( $params && isset( $params['form_params'] ) ){
                    foreach( $params['form_params'] as $key => $value ){
                        $request->addPost( new \EasySwoole\Curl\Field( $key, $value ) );
                    }
                }elseif($params && isset( $params['body'] )){
                    if(!isset($params['header']['Content-Type']) ){
                        $params['header']['Content-Type'] = 'application/json; charset=utf-8';
                    }
                    $request->setUserOpt( [CURLOPT_POSTFIELDS => $params['body']] );
                }
                break;
            default:
                throw new \InvalidArgumentException( "method error" );
                break;
        }
        if( isset( $params['header'] ) && !empty( $params['header'] ) && is_array( $params['header'] ) ){
            foreach( $params['header'] as $key => $value ){
                $string   = "{$key}:$value";
                $header[] = $string;
            }
            $request->setUserOpt( [CURLOPT_HTTPHEADER => $header] );
        }
        /*if (isset($params['cookie'])) {
            $cookie = new \EasySwoole\Curl\Cookie();
            $request->addCookie();
        }*/
        if( isset( $params['opt'] ) && !empty( $params['opt'] ) && is_array( $params['opt'] ) ){
            $request->setUserOpt($params['opt']);
        }
        return $request->exec();
    }
}

function getLogDirByStamp($stamp = 0)
{
    if (empty($stamp)) {
        $stamp = time();
    }
    if (!is_numeric($stamp)) {
        $stamp = strtotime($stamp);
    }
    $logDir = config('LOG_DIR');
    if ($format = config('logger_dir_format')) {
        $logDir .= '/' . date($format, $stamp);
    }
    return rtrim($logDir, '/') . '/';
}

/**
 * 发送公众号模板消息
 * @param $data
 */
function sendWeChatMessge($data)
{
    $tempData = [
        'first' => $data['title'],
        'keyword1' => $data['keyword1'],
        'keyword2' => $data['keyword2'],
        'keyword3' => date('Y年m月d日 H:i:s'),
        'remark' => '查看详情'
    ];;
    WeChatManager::getInstance()->sendTemplateMessage($tempData);
}

/**
 * 发送微信报警
 * @param $msg
 * @param $file
 * @param int $line
 */
function wechatWarning($msg, $file = '', $line = 0)
{
    $data = [
        'title' => "程序发生错误：第{$line}行",
        'keyword1' => "相关文件： {$file}",
        'keyword2' => "相关内容：{$msg}",
    ];
    $time = time();
    $strId = md5(json_encode($data));
    $chkFile = config('LOG_DIR') . '/wechat/checktime_'. date('Ymd') .'.log';
    File::touchFile($chkFile, false);
    $content = file_get_contents($chkFile);
    $arr = json_decode($content, true);
    if ($arr) {
        $last = $arr[$strId]['time'] ?? '';
        $limit = config('wechat.err_limit_time') * 60;
        if ($last && $last > $time - $limit) {
            return;
        }
    }
    $arr[$strId]['time'] = $time;
    file_put_contents($chkFile, json_encode($arr));
    sendWeChatMessge($data);
}