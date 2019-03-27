<?php

namespace svsoft\yii\items\repositories\tables;

use yii\db\Connection;

class TableField extends AbstractTableValue
{
    use TableKeyTrait;

    CONST TABLE = 'items_field';

    protected $all;

    function __construct(Connection $db)
    {
        parent::__construct($db);

        foreach($this->getAll() as $row)
        {
            $this->keys[$row['id']] = $row['key'];
            $this->ids[$row['key']] = $row['id'];
        }
    }

    function getAll()
    {
        if ($this->all === null)
            $this->all = $this->query()->indexBy('key')->orderBy(['sort'=>SORT_ASC])->all();

        return $this->all;
    }

    function table()
    {
        return self::TABLE;
    }

    function resetCache()
    {
        $this->all = null;
    }

}
