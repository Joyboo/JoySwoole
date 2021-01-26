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

        $this->doWarning();
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

        $this->doWarning();
    }

    public function doWarning()
    {
        // todo 报警
    }
}