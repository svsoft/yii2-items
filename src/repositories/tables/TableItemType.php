<?php

namespace svsoft\yii\items\repositories\tables;

use yii\db\Connection;

class TableItemType extends AbstractTableValue
{
    use TableKeyTrait;

    CONST TABLE = 'items_item_type';

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
            $this->all = $this->query()->indexBy('key')->all();

        return $this->all;
    }

    function table()
    {
        return self::TABLE;
    }
}
