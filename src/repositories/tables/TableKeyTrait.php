<?php

namespace svsoft\yii\items\repositories\tables;

/**
 * Trait TableKeyTrait
 * @method \yii\db\Query query
 */
trait TableKeyTrait
{
    protected $keys = [];
    protected $ids = [];

    function getKey($id)
    {
        if (isset($this->keys[$id]))
            return $this->keys[$id];

        if (!$row = $this->query()->andWhere(['id' =>$id])->one())
            return null;

        $this->keys[$id] = (int)$row['key'];

        return $this->getKey($id);
    }

    function getId($key)
    {
        if (isset($this->ids[$key]))
            return $this->ids[$key];

        if (!$row = $this->query()->andWhere(['key' =>$key])->one())
            return null;

        $this->ids[$key] = $row['id'];

        return $this->ids[$key];
    }
}