<?php


namespace App\Command;

use EasySwoole\Command\AbstractInterface\CommandInterface;

/**
 * 查看规则 php easyswoole example -h
 * Class Example
 * @package App\Command
 */
class Example implements CommandInterface
{
    public function commandName(): string
    {
        return 'example';
    }

    public function desc(): string
    {
        return '这是一个示例';
    }

    public function exec(): string
    {
        /** 获取原始未变化的argv */
        $oriArgv = \EasySwoole\Command\CommandManager::getInstance()->getOriginArgv();
//        var_dump($oriArgv);

        /**
         * 经过处理的数据
         * 比如 1 2 3 a=1 aa=123
         * 处理之后就变成[1, 2, 3, 'a' => 1, 'aa' => 123]
         */
        $args = \EasySwoole\Command\CommandManager::getInstance()->getArgs();
//        var_dump($args);

        /**
         * 获取选项
         * 比如 --config=dev -d
         * 处理之后就是['config' => 'dev', 'd' => true]
         */
        $options = \EasySwoole\Command\CommandManager::getInstance()->getOpts();
//        var_dump($options);

        /**
         * 根据下标或者键来获取值
         * a=123
         */
        $a = \EasySwoole\Command\CommandManager::getInstance()->getArg('a');
//        var_dump($a);

        /**
         * 根据键来获取选项
         */
        \EasySwoole\Command\CommandManager::getInstance()->getOpt('config');

        /**
         * 检测在args中是否存在该下标或者键
         */
        \EasySwoole\Command\CommandManager::getInstance()->issetArg(1);

        /**
         * 检测在opts中是否存在该键
         */
//        \EasySwoole\Command\CommandManager::getInstance()->issetOpt();

        return '自定义命令行执行方法';
    }

    public function help(\EasySwoole\Command\AbstractInterface\CommandHelpInterface $commandHelp): \EasySwoole\Command\AbstractInterface\CommandHelpInterface
    {
        $commandHelp->addAction('test','测试方法');
        $commandHelp->addActionOpt('-no','不输出详细信息');
        return $commandHelp;
    }
}