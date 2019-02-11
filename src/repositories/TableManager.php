<?php

namespace svsoft\yii\items\repositories;

use svsoft\yii\items\repositories\tables\TableField;
use svsoft\yii\items\repositories\tables\TableItem;
use svsoft\yii\items\repositories\tables\TableItemType;
use svsoft\yii\items\repositories\tables\TableValue;
use yii\db\Connection;

class TableManager
{
    /**
     * @var TableValue
     */
    private $tableValue;

    /**
     * @var TableField
     */
    private $tableField;

    /**
     * @var TableItem
     */
    private $tableItem;

    /**
     * @var TableItemType
     */
    private $tableItemType;

    /**
     * @var Connection
     */
    private $db;

    function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @return TableValue
     */
    function getTableValue()
    {
        if ($this->tableValue === null)
            $this->tableValue = new TableValue($this->db);

        return $this->tableValue;
    }

    /**
     * @return TableField
     */
    function getTableField()
    {
        if ($this->tableField === null)
            $this->tableField = new TableField($this->db);

        return $this->tableField;
    }

    /**
     * @return TableItemType
     */
    function getTableItemType()
    {
        if ($this->tableItemType === null)
            $this->tableItemType = new TableItemType($this->db);

        return $this->tableItemType;
    }

    /**
     * @return TableItem
     */
    function getTableItem()
    {
        if ($this->tableItem === null)
            $this->tableItem = new TableItem($this->db);

        return $this->tableItem;
    }

    /**
     * @return Connection
     */
    function getDb()
    {
        return $this->db;
    }

    function beginTransaction()
    {
        if ($this->db->transaction)
            return $this->db->transaction;

        return $this->db->beginTransaction();
    }

}
