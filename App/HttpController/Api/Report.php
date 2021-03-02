<?php


namespace App\HttpController\Api;

use App\HttpController\ApiBase;
use App\Queue\RedisQueue;
use EasySwoole\Component\Di;
use EasySwoole\Http\Message\Status;
use EasySwoole\Queue\Job;

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

    /**
     * push数据到队列
     */
    public function push()
    {
        $job = new Job();
        $job->setJobData(['test'=>'测试','time'=>time()]);
        $result = RedisQueue::getInstance()->producer()->push($job);
        $this->writeJson(Status::CODE_OK, Status::getReasonPhrase(Status::CODE_OK), $result);
    }
}