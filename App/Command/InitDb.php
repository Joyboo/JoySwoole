<?php


namespace App\Command;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;


/**
 * php easyswoole initdb
 * Class Example
 * @package App\InitDb
 */
class InitDb extends Base
{
    public function desc(): string
    {
        return '初始化数据库';
    }

    public function exec(): string
    {
        /** 获取原始未变化的argv */
//        $oriArgv = $this->CommandManager()->getOriginArgv();
//        var_dump($oriArgv);

        /**
         * 经过处理的数据
         * 比如 1 2 3 a=1 aa=123
         * 处理之后就变成[1, 2, 3, 'a' => 1, 'aa' => 123]
         */
//        $args = $this->CommandManager()->getArgs();
//        var_dump($args);

        /**
         * 获取选项
         * 比如 --config=dev -d
         * 处理之后就是['config' => 'dev', 'd' => true]
         */
//        $options = $this->CommandManager()->getOpts();
//        var_dump($options);

        /**
         * 根据下标或者键来获取值
         * a=123
         */
//        $a = $this->CommandManager()->getArg('a');
//        var_dump($a);

        /**
         * 根据键来获取选项
         */
//        $this->CommandManager()->getOpt('config');

        /**
         * 检测在args中是否存在该下标或者键
         */
//        $this->CommandManager()->issetArg(1);

        /**
         * 检测在opts中是否存在该键
         */
//        $this->CommandManager()->issetOpt();


        go(function() {
            $file = EASYSWOOLE_ROOT . '/db_init.sql';
            if (!is_file($file)) {
                return;
            }
            $this->mysqliClient->rawQuery(file_get_contents($file));
            echo $this->desc() . " 完成! \n";
        });

        return $this->desc();
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        $commandHelp->addAction('test','测试方法');
        $commandHelp->addActionOpt('-no','不输出详细信息');
        return $commandHelp;
    }
}