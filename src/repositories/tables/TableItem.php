<?php

namespace svsoft\yii\items\repositories\tables;

class TableItem extends AbstractTableValue
{
    use TableKeyTrait;

    CONST TABLE = 'items_item';

    function table()
    {
        return self::TABLE;
    }
}
