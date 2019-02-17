<?php

namespace svsoft\yii\items\widgets;

use svsoft\yii\items\entities\Field;
use svsoft\yii\items\entities\ItemType;
use yii\base\InvalidCallException;
use yii\grid\GridView;

class ItemGridView extends GridView
{
    /**
     * @var ItemType
     */
    public $itemType;


    function init()
    {
        if (!$this->itemType)
            throw new InvalidCallException('Property itemType must be set');


        $additionalColumns[] = 'id';

        foreach($this->itemType->getFields() as $field)
        {
            if ($field->getType()->getId() != Field::TYPE_FILE)
                $additionalColumns[] = $field->getName();
        }

        $this->columns = array_merge($additionalColumns, $this->columns);

        parent::init();
    }

}