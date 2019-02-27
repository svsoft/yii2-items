<?php

namespace svsoft\yii\items\repositories\tables;


use svsoft\yii\items\entities\Field;
use yii\db\Connection;

class TableValue extends AbstractTableValue
{
    CONST TABLE = 'items_value';

    private $valueColumns;

    function __construct(Connection $db)
    {
        $this->valueColumns = [
            Field::TYPE_INT    => 'value_int',
            Field::TYPE_STRING => 'value_string',
            Field::TYPE_TEXT   => 'value_text',
            Field::TYPE_REAL   => 'value_real',
            Field::TYPE_FILE   => 'value_string',
            Field::TYPE_HTML   => 'value_text',
            Field::TYPE_ITEM   => 'value_item',
            Field::TYPE_DATE   => 'value_date',
        ];

        parent::__construct($db);
    }

    function table()
    {
        return self::TABLE;
    }

    function getValueColumn($type)
    {
        return $this->valueColumns[$type];
    }

    /**
     * Меняет поле хранения значений поля
     *
     * @param $fieldKey
     * @param $oldType
     * @param $newType
     *
     * @throws \yii\db\Exception
     */
    function changeType($fieldKey, $oldType, $newType)
    {
        $oldValueColumn = $this->getValueColumn($oldType);
        $valueColumn = $this->getValueColumn($newType);

        $this->db->createCommand()
            ->update($this->table(),[$valueColumn => new \yii\db\Expression($oldValueColumn), $oldValueColumn=>null],['field_key'=>$fieldKey])
            ->execute();
    }
}
