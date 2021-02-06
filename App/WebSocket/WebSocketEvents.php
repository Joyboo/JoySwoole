<?php

namespace App\WebSocket;

use Swoole\WebSocket\Server;

/**
 * Class WebSocketEvents
 * @package App\WebSocket
 */
class WebSocketEvents
{

    /**
     * @param Server $server
     * @param \Swoole\Http\Request $request
     */
    public static function onOpen(Server $server, \Swoole\Http\Request $request)
    {
        //绑定fd变更状态
//        Cache::getInstance()->set('uid' . $user['id'], ["value" => $request->fd], 3600);
//        Cache::getInstance()->set('fd' . $request->fd, ["value" => $user['id']], 3600);
        var_dump($request->fd, $request->server);
//        $server->push($request->fd, '返回onopen数据:' . $request->fd);
    }

    /**
     * 链接被关闭时
     * @param \Swoole\Server $server
     * @param int $fd
     * @param int $reactorId
     * @throws \Exception
     */
    public static function onClose(\Swoole\Server $server, int $fd, int $reactorId)
    {
        echo "client-{$fd} is closed\n";
    }
}
