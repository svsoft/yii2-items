<?php

namespace svsoft\yii\items\factories;

use svsoft\yii\items\entities\Item;
use svsoft\yii\items\entities\ItemType;

class ItemFactory
{
    private function generateId()
    {
        return uniqid(bin2hex(random_bytes(1)));
    }

    function build(ItemType $itemType)
    {
        $attributes = [];
        foreach($itemType->getFields() as $field)
        {
            $attributes[$field->getName()] = $field->getMultiple() ? [] : null;
        }

        return new Item($this->generateId(), $itemType->getId(), $attributes);
    }

}