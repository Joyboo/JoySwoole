<?php

namespace App\Models;

class Install extends Base
{
    protected $connectionName = 'central_log';

    /*************** ORM模型事件 ****************/
    /**
     * insert前置事件
     * @param Install $model 当前模型实例
     * @return bool
     */
    protected static function onBeforeInsert($model)
    {
    }

    /**
     * insert后置事件
     * @param Install $model 当前模型实例
     * @param bool $res 当前行为执行结果, 当执行失败时类型统一为bool型false
     */
    protected static function onAfterInsert($model, $res)
    {
    }

    protected static function onBeforeUpdate($model)
    {
    }

    protected static function onAfterUpdate($model, $res)
    {
    }

    protected static function onBeforeDelete($model)
    {
    }

    /**
     * @param Install $model
     * @param int $res 这个很特殊,int型 影响记录数
     */
    public static function onAfterDelete($model, $res)
    {

    }
}