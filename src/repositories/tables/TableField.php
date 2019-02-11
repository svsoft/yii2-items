<?php

namespace svsoft\yii\items\repositories\tables;

class TableField extends AbstractTableValue
{
    use TableKeyTrait;

    CONST TABLE = 'items_field';

    function table()
    {
        return self::TABLE;
    }

}
