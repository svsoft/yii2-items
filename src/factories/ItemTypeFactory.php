<?php

namespace svsoft\yii\items\factories;

use svsoft\yii\items\entities\ItemType;

class ItemTypeFactory
{
    private function generateId()
    {
        return uniqid(bin2hex(random_bytes(1)));
    }

    function build($name, $fields = [])
    {
        return new ItemType($this->generateId(), $name, $fields);
    }

}