<?php

namespace svsoft\yii\items\repositories\tables;

/**
 * Trait TableKeyTrait
 * @method \yii\db\Query query
 */
trait TableKeyTrait
{
    function getKey($id)
    {
        if (!$row = $this->query()->andWhere(['id' =>$id])->one())
            return null;

        return (int)$row['key'];
    }

    function getId($key)
    {
        if (!$row = $this->query()->andWhere(['key' =>$key])->one())
            return null;

        return $row['id'];
    }
}