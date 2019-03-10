<?php

namespace svsoft\yii\items\factories;

use svsoft\yii\items\entities\ItemType;
use svsoft\yii\items\models\SaveItemModel;

class SaveModelFactory
{
    private $classes = [];

    function __construct($classes = [])
    {
        $this->classes = $classes;
    }

    private function getFormClass(ItemType $itemType)
    {
        if (isset($this->classes[$itemType->getName()]))
            $class = $this->classes[$itemType->getName()];
        else
            $class = SaveItemModel::class;

        return $class;
    }

    /**
     * @param ItemType $itemType
     *
     * @return SaveItemModel|object
     * @throws \yii\base\InvalidConfigException
     */
    function build(ItemType $itemType)
    {
        return \Yii::createObject($this->getFormClass($itemType), [$itemType]);
    }

}