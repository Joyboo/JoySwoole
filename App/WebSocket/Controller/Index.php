<?php


namespace App\WebSocket\Controller;

use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\Socket\AbstractInterface\Controller;
use Swoole\WebSocket\Server;


class Index extends Controller
{
    /** @var Server $server */
    protected $server;

    public function __construct()
    {
        parent::__construct();
        $this->server = ServerManager::getInstance()->getSwooleServer();
    }

    public function index()
    {
        $info = $this->caller()->getArgs();
        $fd = $this->caller()->getClient()->getFd();

        if ($this->server->isEstablished($fd)) {
            $this->server->push($fd, json_encode($info));
        }
    }

    public function test()
    {
        $info = $this->caller()->getArgs();

        // 遍历全部连接
        $start_fd = 0;
        while (true) {
            $conn_list = $this->server->getClientList($start_fd, 10);
            if ($conn_list === false || count($conn_list) === 0) {
                break;
            }
            $start_fd = end($conn_list);
            foreach ($conn_list as $fd) {
                // 该连接的信息
//                $fdinfo = $this->server->getClientInfo($fd);
                if ($this->server->isEstablished($fd)) {
                    $this->server->push($fd, json_encode($info));
                }
            }
        }
    }
}