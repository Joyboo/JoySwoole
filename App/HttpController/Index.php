<?php


namespace App\HttpController;


class Index extends Base
{
    function index()
    {
        $file = EASYSWOOLE_ROOT.'/vendor/easyswoole/easyswoole/src/Resource/Http/welcome.html';
        if(!is_file($file)){
            $file = EASYSWOOLE_ROOT.'/src/Resource/Http/welcome.html';
        }
        $this->response()->write(file_get_contents($file));
    }

    public function welcome()
    {
        $file = EASYSWOOLE_ROOT.'/vendor/easyswoole/easyswoole/src/Resource/Http/welcome.html';
        if(!is_file($file)){
            $file = EASYSWOOLE_ROOT.'/src/Resource/Http/welcome.html';
        }
        $this->response()->write(file_get_contents($file));
    }

    public function test()
    {
        $this->response()->write('this is test');
    }

    /**
     * 用于校验微信配置的token
     * @doc https://developers.weixin.qq.com/doc/offiaccount/Basic_Information/Access_Overview.html
     */
    public function wechatToken()
    {
        $this->ajaxReturn($this->request()->getRequestParam('echostr'));
    }
}