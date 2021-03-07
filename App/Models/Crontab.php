<?php


namespace App\Models;

use Swoole\Coroutine;

class Crontab extends Base
{
    protected $connectionName = 'new_central';

    public function getCrontab($gid = 0)
    {
        return $this->where('status', 0)->where("(gid = 0 or FIND_IN_SET ({$gid},gid) > 0)")->all();
    }

    public function index()
    {
        $this->timelock(__METHOD__);
    }

    public function test()
    {
        $this->timelock(__METHOD__);
    }

    public function test1()
    {
        $this->timelock(__METHOD__);
    }

    public function test2()
    {
        $this->timelock(__METHOD__);
    }

    /**
     * 测试是否阻塞
     * @param $name
     * @param int $sec
     */
    protected function timelock($name, $sec = 61)
    {
        while ($sec > 0) {
            var_dump("this is Models {$name}: $sec");
            Coroutine::sleep(1);
            --$sec;
        }
    }
}