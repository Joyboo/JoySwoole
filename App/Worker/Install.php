<?php


namespace App\Worker;

use \App\Models\Install as InstallModel;
use Swoole\Process;

class Install extends Base
{
    public function run($arg)
    {

    }

    // 父子进程管道通信
    protected function onPipeReadable(Process $process)
    {
        // 该回调可选
        // 当主进程对子进程发送消息的时候 会触发
        $recvMsgFromMain = $process->read(); // 用于获取主进程给当前进程发送的消息
        $param = json_decode($recvMsgFromMain, true);
        if (!is_array($param)) {
            trace(__METHOD__ . "传参数错误 ：" . $recvMsgFromMain);
            return;
        }

        if (!is_array($param['data'])) {
            $param['data'] = explode('|', $param['data']);
        }
        $args = $this->getArg();
        $field = explode('|', $args['param_list']);

        $data = [];
        foreach ($field as $key => $value) {
            if (isset($param['data'][$key])) {
                $data[$value] = $param['data'][$key];
            }
        }

        go(function () use ($data, $param) {
            $result = InstallModel::create()->data($data, true)->save();
            if (!$result) {
                logger()->error($data, "report_error");
            }
        });
    }
}