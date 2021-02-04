<?php


namespace App\Models;

class Crontab extends Base
{
    protected $connectionName = 'new_central';

    public function getCrontab($gid = 0)
    {
        return $this->where('status', 0)->where("(gid = 0 or FIND_IN_SET ({$gid},gid) > 0)")->all();
    }
}