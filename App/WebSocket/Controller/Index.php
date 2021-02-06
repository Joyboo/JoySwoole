<?php


namespace App\WebSocket\Controller;

use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\Socket\AbstractInterface\Controller;
use Swoole\WebSocket\Server;


class Index extends Controller
{
    public function index()
    {
    }

    public function test()
    {
        $info = $this->caller()->getArgs();
        /** @var Server $server */
        $server = ServerManager::getInstance()->getSwooleServer();

        // 遍历全部连接
        $start_fd = 0;
        while (true) {
            $conn_list = $server->getClientList($start_fd, 10);
            if ($conn_list === false || count($conn_list) === 0) {
                break;
            }
            $start_fd = end($conn_list);
            foreach ($conn_list as $fd) {
                // 该连接的信息
//                $fdinfo = $server->getClientInfo($fd);
                if ($server->isEstablished($fd)) {
                    $server->push($fd, json_encode($info));
                }
            }
        }
    }
}