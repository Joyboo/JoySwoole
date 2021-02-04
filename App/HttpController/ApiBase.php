<?php


namespace App\HttpController;

use EasySwoole\EasySwoole\Trigger;
use EasySwoole\Http\Message\Status;

abstract class ApiBase extends Base
{
    protected function writeJson($statusCode = 200, $msg = null, $result = null)
    {
        return parent::writeJson($statusCode, $result, $msg);
    }

    protected function actionNotFound(?string $action): void
    {
        $this->writeJson(Status::CODE_NOT_FOUND);
    }

    function onException(\Throwable $throwable): void
    {
        if (runEnvDev()) {
            throw $throwable;
        } else {
            Trigger::getInstance()->throwable($throwable);
            $this->writeJson(Status::CODE_INTERNAL_SERVER_ERROR, '系统内部错误，请稍后重试');
        }
    }
}