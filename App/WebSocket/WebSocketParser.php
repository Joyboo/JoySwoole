<?php

namespace App\WebSocket;

use EasySwoole\Socket\AbstractInterface\ParserInterface;
use EasySwoole\Socket\Client\WebSocket;
use EasySwoole\Socket\Bean\Caller;
use EasySwoole\Socket\Bean\Response;

/**
 * WebSocket解析器
 * Class WebSocketParser
 * @package App\WebSocket
 */
class WebSocketParser implements ParserInterface
{
    /**
     * decode
     * @param  string         $raw    客户端原始消息
     * @param  WebSocket      $client WebSocket Client 对象
     * @return Caller         Socket  调用对象
     */
    public function decode($raw, $client) : ? Caller
    {
        // 解析 客户端原始消息
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            logger()->error("WebSocket decode message error: " . var_export($data, true), 'error');;
            return null;
        }
        // new 调用者对象
        $caller =  new Caller();

        $class = '\\App\\WebSocket\\Controller\\'. ucfirst($data['class'] ?? 'Index');
        if (!class_exists($class)) {
            logger()->error("WebSocket Controller not fount: {$class}", 'error');
            return null;
        }
        $caller->setControllerClass($class);

        $action = $data['action'] ?? 'index';
        if (!method_exists($class, $action)) {
            logger()->error("WebSocket Action not fount: {$class}.{$action}", 'error');
            return null;
        }
        // 设置被调用的方法
        $caller->setAction($action);

        // 设置被调用的Args
        $args = ($data && is_array($data)) ? $data : [];
        $caller->setArgs($args);
        return $caller;
    }
    /**
     * encode
     * @param  Response $response Socket Response 对象
     * @param  WebSocket $client WebSocket Client 对象
     * @return string 发送给客户端的消息
     */
    public function encode(Response $response, $client) : ? string
    {
        /**
         * 这里返回响应给客户端的信息
         * 这里应当只做统一的encode操作 具体的状态等应当由 Controller处理
         */
        return $response->getMessage();
    }
}