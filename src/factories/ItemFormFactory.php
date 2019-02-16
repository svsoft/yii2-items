<?php

namespace svsoft\yii\items\factories;

use svsoft\yii\items\entities\ItemType;
use svsoft\yii\items\forms\ItemForm;

class ItemFormFactory
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
            $class = ItemForm::class;

        return $class;
    }
    /**
     * @param ItemType $itemType
     *
     * @return ItemForm
     */
    function build(ItemType $itemType)
    {
        /** @var ItemForm $form */
        $form = \Yii::createObject($this->getFormClass($itemType), [$itemType]);

        return $form;
    }

}