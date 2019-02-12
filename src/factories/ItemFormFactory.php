<?php

namespace svsoft\yii\items\factories;

use svsoft\yii\items\entities\ItemType;
use svsoft\yii\items\entities\Field;
use svsoft\yii\items\forms\ItemForm;

class ItemFormFactory
{
    /**
     * @var ItemType
     */
    protected $itemType;

    function __construct(ItemType $itemType)
    {
        $this->itemType = $itemType;
    }

    /**
     * @return ItemForm
     */
    function build()
    {
        $fields = $this->itemType->getFields();

        $fieldNames = [];
        foreach($fields as $field)
            $fieldNames[] = $field->getName();

        $form = new ItemForm($fieldNames, $this->itemType);
        foreach($this->itemType->getFields() as $field)
        {
            $name = $field->getName();
            switch($field->getType())
            {
                case Field::TYPE_STRING :
                    $form->addRule($name, 'string', ['max'=>255]);
                    break;
                case Field::TYPE_TEXT :
                    $form->addRule($name, 'string');
                    break;
                case Field::TYPE_INT :
                    $form->addRule($name, 'integer');
                    break;
                case Field::TYPE_REAL :
                    $form->addRule($name, 'number');
                    break;
                case Field::TYPE_FILE :
                    $form->addRule($name, 'file', ['maxFiles'=>$field->getMultiple() ? 10 : 1]);
                    break;
            }
        }

        return $form;
    }

}