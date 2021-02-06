<?php

namespace App\WebSocket;

use EasySwoole\EasySwoole\ServerManager;
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
            // 是否需要关闭连接???
            $this->respClient($client, Response::STATUS_OK, "WebSocket decode message error: " . var_export($raw, true));
            return null;
        }
        // new 调用者对象
        $caller =  new Caller();

        $class = '\\App\\WebSocket\\Controller\\'. ucfirst($data['class'] ?? 'Index');
        if (!class_exists($class)) {
            $this->respClient($client, Response::STATUS_OK, "WebSocket Controller not fount: {$class}");
            return null;
        }
        $caller->setControllerClass($class);

        $action = $data['action'] ?? 'index';
        if (!method_exists($class, $action)) {
            $this->respClient($client, Response::STATUS_OK, "WebSocket Action not fount: {$class}.{$action}");
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

    protected function respClient($client, $status, $message)
    {
        $server = ServerManager::getInstance()->getSwooleServer();
        $response = new Response([
            'status' => $status,
            'message' => $message
        ]);
        $data = $this->encode($response, $client);
        if (is_null($data)) {
            return;
        }
        logger()->error($data, 'error');
        $fd = $client->getFd();
        if ($server->isEstablished($fd)) {
            $server->push($fd, $data,$response->getOpCode(),$response->isFinish());
        }
    }
}