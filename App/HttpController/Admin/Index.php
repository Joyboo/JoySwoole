<?php

namespace App\HttpController;

use App\Model\FriendGroupModel;
use App\Model\GroupMemberModel;
use App\Model\UserModel;
use EasySwoole\Http\Message\Status;
use EasySwoole\Http\Message\UploadFile;
use EasySwoole\Redis\Redis;
use EasySwoole\RedisPool\RedisPool;
use EasySwoole\VerifyCode\Conf;


class Index extends Base
{
    public function test()
    {
        $this->render('websocket');
    }

    public function index()
    {
        $token = $this->request()->getRequestParam('token');

        $RedisPool = RedisPool::defer();
        $user = $RedisPool->get('User_token_' . $token);

        if (!$user) {
            $this->response()->redirect("/login");
        }

        $user = json_decode($user, true);
        $hostName = 'ws://' . $this->request()->getServerParams()['remote_addr'] . ':9501';
        $this->render('index', ['server' => $hostName, 'token' => $token, 'user' => $user]);
    }

    public function login()
    {
        $this->render('login');
    }

    /**
     * 注册
     */
    public function register()
    {
        $code_hash = uniqid() . uniqid();
        $this->render('register', ['code_hash' => $code_hash]);
    }

    /**
     * 验证码
     */
    public function getCode()
    {
        $params = $this->request()->getRequestParam();
        $key = $params['key'];

        $config = new Conf();
        $code = new \EasySwoole\VerifyCode\VerifyCode($config);
        $num = mt_rand(000, 999);

        RedisPool::invoke(function (Redis $redis) use ($key, $num) {
            $redis->set('Code' . $key, $num, 1000);
        });

        $this->response()->withHeader('Content-Type', 'image/png');
        $this->response()->write($code->DrawCode($num)->getImageByte());
    }

    /**
     * 上传图片
     */
    public function upload()
    {
        /** @var UploadFile $img_file */
        $img_file = $this->request()->getUploadedFile('file');

        if (!$img_file) {
            $this->writeJson(Status::CODE_INTERNAL_SERVER_ERROR, null, '请选择上传的文件');
        }

        if ($img_file->getSize() > 1024 * 1024 * 5) {
            $this->writeJson(Status::CODE_INTERNAL_SERVER_ERROR, null, '图片不能大于5M！');
        }

        $MediaType = explode("/", $img_file->getClientMediaType());
        $MediaType = $MediaType[1] ?? "";
        if (!in_array($MediaType, ['png', 'jpg', 'gif', 'jpeg', 'pem', 'ico'])) {
            $this->writeJson(Status::CODE_INTERNAL_SERVER_ERROR, null, '文件类型不正确！');
        }

        $path = '/public/upload/';
        $dir = EASYSWOOLE_ROOT . $path;
        $fileName = $img_file->getClientFileName() . '_' . uniqid();

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        try {
            $img_file->moveTo($dir . $fileName);

            $data = [
                'name' => $fileName,
                'src' => $path . $fileName,
            ];
            $this->writeJson(Status::CODE_OK, $data, '上传成功');
        } catch (\Throwable $throwable) {
            $this->writeJson(Status::CODE_INTERNAL_SERVER_ERROR, null, '上传失败');
        }
    }
}
