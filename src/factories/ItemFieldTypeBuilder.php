<?php
namespace svsoft\yii\items\factories;

class ItemFieldTypeBuilder extends FieldTypeBuilder
{
    protected $id;

    protected $multiple;

    protected $required;

    protected $itemTypeName;

    /**
     * @var FieldTypeFactory
     */
    protected $fieldTypeFactory;

    /**
     * @param $itemTypeName
     *
     * @return $this
     */
    function setItemType($itemTypeName)
    {
        $this->itemTypeName = $itemTypeName;

        return $this;
    }
}