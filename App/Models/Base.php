<?php


namespace App\Models;

use EasySwoole\ORM\AbstractModel;

abstract class Base extends AbstractModel
{
    protected $tableName = '';

    public function __construct(array $data = [])
    {
        parent::__construct($data);

        if (empty($this->tableName)) {
            $this->tableName = $this->_getTable();
        }
    }

    /**
     * 获取表名，并将将Java风格转换为C的风格
     * @return string
     */
    protected function _getTable()
    {
        $name = basename(str_replace('\\', '/', get_called_class()));
        return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
    }

    /**
     * 暂未找到easyswoole的批量setAttr方法
     * @param array $data
     */
    public function setAttrMultiple($data = [])
    {
        if (empty($data)) {
            return;
        }
        foreach ($data as $key => $val) {
            $this->setAttr($key, $val);
        }
    }

    public function getAll(int $page = 1, int $pageSize = 10, string $field = '*'): array
    {
        $list = $this
            ->withTotalCount()
            ->order($this->schemaInfo()->getPkFiledName(), 'DESC')
            ->field($field)
            ->limit($pageSize * ($page - 1), $pageSize)
            ->all();
        $total = $this->lastQueryResult()->getTotalCount();;
        return ['total' => $total, 'list' => $list];
    }

}