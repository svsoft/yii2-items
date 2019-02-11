<?php

namespace svsoft\yii\items\repositories\tables;

class TableItemType extends AbstractTableValue
{
    use TableKeyTrait;

    CONST TABLE = 'items_item_type';

    function table()
    {
        return self::TABLE;
    }
}
