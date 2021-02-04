<?php


namespace App\HttpController;

use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\Http\Message\Status;

abstract class Base extends Controller
{
    protected function onRequest(?string $action): ?bool
    {
        // 权限不通过 / 请登录
        /*$this->response()->withHeader('Content-type', 'application/json;charset=utf-8');
        $this->response()->write("没有权限");
        $this->response()->withStatus(Status::CODE_FORBIDDEN);
        return false;*/
        return true;
    }

    function onException(\Throwable $throwable): void
    {
        $error = [
            'message' => $throwable->getMessage(),
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
            'trace' => $throwable->getTrace()
        ];

        trace($error, 'error');
        if (runEnvDev()) {
            $this->response()->getStatusCode(Status::CODE_INTERNAL_SERVER_ERROR);
            $this->response()->write("<pre>" . var_export($error, true) . "</pre>");
        }
    }
    protected function actionNotFound(?string $action): void
    {
        $this->response()->withStatus(404);
        $file = EASYSWOOLE_ROOT.'/vendor/easyswoole/easyswoole/src/Resource/Http/404.html';
        if(!is_file($file)){
            $file = EASYSWOOLE_ROOT.'/src/Resource/Http/404.html';
        }
        $this->response()->write(file_get_contents($file));
    }
}