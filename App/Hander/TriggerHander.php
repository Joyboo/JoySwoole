<?php


namespace App\Hander;

use EasySwoole\Trigger\Location;
use EasySwoole\Trigger\TriggerInterface;

class TriggerHander implements TriggerInterface
{
    public function error($msg, int $errorCode = E_USER_ERROR, Location $location = null)
    {
        if($location == null){
            $location = new Location();
            $debugTrace = debug_backtrace();
            $caller = array_shift($debugTrace);
            $location->setLine($caller['line']);
            $location->setFile($caller['file']);
        }
        logger()->error("{$msg} , file:{$location->getFile()} line:{$location->getLine()}", 'error');

        $this->doWarning($msg, $location->getFile(), $location->getLine());
    }

    public function throwable(\Throwable $throwable)
    {
        $msg = [
            'message' => $throwable->getMessage(),
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
            'trace' => $throwable->getTrace()
        ];
        logger()->error(json_encode($msg));

        $this->doWarning($msg['message'], $msg['file'], $msg['line']);
    }

    public function doWarning($msg, $file, $line = 0)
    {
        // todo 相同的内容X分钟内不报警
        sendWeChatMessge([
            'title' => "程序发生错误：第{$line}行",
            'keyword1' => "文件： {$file}",
            'keyword2' => "相关内容：{$msg}",
        ]);
    }
}