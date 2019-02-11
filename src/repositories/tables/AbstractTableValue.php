<?php

namespace svsoft\yii\items\repositories\tables;

use yii\db\Connection;
use yii\db\Query;

abstract class AbstractTableValue implements TableInterface
{
    /**
     * @var Connection
     */
    protected $db;

    function __construct(Connection $db)
    {
        $this->db = $db;
    }

    abstract function table();

    /**
     * @return Query
     */
    function query()
    {
        return (new Query())->from($this->table());
    }

    final function update($columns, $condition = '', $params = [])
    {
        return $this->db->createCommand()->update($this->table(), $columns, $condition, $params)->execute();
    }

    final function insert($columns)
    {
        return $this->db->createCommand()->insert($this->table(), $columns)->execute();
    }

    final function delete($condition = '', $params = [])
    {
        return $this->db->createCommand()->delete($this->table(), $condition, $params)->execute();
    }

}
