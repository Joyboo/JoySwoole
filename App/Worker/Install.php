<?php


namespace App\Worker;

use \App\Models\Install as InstallModel;

class Install extends Base
{
    public function exec($task)
    {
        if (!is_array($task)) {
            return;
        }

        InstallModel::create()->data($task, true)->save();
    }
}