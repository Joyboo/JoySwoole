<?php


namespace App\HttpController\Api;

use App\HttpController\ApiBase;
use EasySwoole\Component\Di;
use EasySwoole\Http\Message\Status;

class Report extends ApiBase
{
    public function index()
    {
        $data = $this->request()->getRequestParam('counter', 'data');
        $counter = ucfirst($data['counter']);
        $customProcess = Di::getInstance()->get($counter);
        if (empty($customProcess)) {
            $this->writeJson(Status::CODE_BAD_REQUEST, "counter error: {$counter}");
            return;
        }

        // 向自定义进程中传输信息，会触发自定义进程的 onPipeReadable 回调
        $customProcess->write(json_encode($data));
        $this->writeJson(Status::CODE_OK, Status::getReasonPhrase(Status::CODE_OK));
    }
}