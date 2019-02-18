<?php

namespace svsoft\yii\items\factories;

use svsoft\yii\items\entities\Item;
use svsoft\yii\items\entities\ItemType;

class ItemFactory
{
    private $classes = [];

    function __construct($classes = [])
    {
        $this->classes = $classes;
    }

    private function getClass(ItemType $itemType)
    {
        if (isset($this->classes[$itemType->getName()]))
            $class = $this->classes[$itemType->getName()];
        else
            $class = Item::class;

        return $class;
    }

    /**
     * @param $id
     * @param ItemType $itemType
     * @param $attributes
     *
     * @return Item
     * @throws \yii\base\InvalidConfigException
     */
    function build($id, ItemType $itemType, $attributes)
    {
        /** @var Item $instance */
        $instance = \Yii::createObject($this->getClass($itemType), [$id, $itemType->getId(), $attributes]);

        return $instance;
    }

}