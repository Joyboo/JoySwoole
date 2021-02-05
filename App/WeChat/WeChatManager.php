<?php


namespace App\WeChat;

use EasySwoole\Component\Singleton;
use EasySwoole\WeChat\WeChat;
use RuntimeException;
use EasySwoole\WeChat\Bean\OfficialAccount\TemplateMsg;

/**
 * Class WeChatManager
 * @package App\WeChat
 */
class WeChatManager
{

    use Singleton;

    /**
     * @var array 存储全部WeChat对象
     */
    private $weChatList = [];

    /**
     * 注册WeChat实例
     * @param string $name  实例名称
     * @param WeChat $weChat WeChat实例对象
     */
    public function register(string $name, WeChat $weChat): void
    {
        if (!isset($this->weChatList[$name])) {
            $this->weChatList[$name] = $weChat;
        } else {
            throw new RuntimeException('重复注册weChat.');
        }
    }

    /**
     * 获取WeChat实例
     * @param string $name 实例名称-传入该参数获取对应实例
     *
     * @return WeChat 返回WeChat实例对象
     */
    public function weChat(string $name): WeChat
    {
        if (isset($this->weChatList[$name])) {
            return $this->weChatList[$name];
        }

        throw new RuntimeException('not found weChat name');
    }

    public function sendTemplateMessage($data, $openid = '', $tmpId = '')
    {
        $templateMsg = new TemplateMsg();
        if (empty($openid)) {
            $openid = config('wechat.touser');
        }
        if (empty($tmpId)) {
            $tmpId = config('wechat.templateId');
        }
        $templateMsg->setTouser($openid);
        // 设置所需跳转到的小程序appid
//        $templateMsg->setAppid('xiaochengxuappid12345');
        // 设置所需跳转到的小程序路径
//        $templateMsg->setPagepath('index?foo=bar');

        $templateMsg->setUrl(config('wechat.url'));
        $templateMsg->setTemplateId($tmpId);

        $templateMsg->setData($data);

        $wechat = $this->weChat('default');
        if (! $wechat->officialAccount()->accessToken()->getToken()) {
            $wechat->officialAccount()->accessToken()->refresh();
        }
        $wechat->officialAccount()->templateMsg()->send($templateMsg);
    }
}