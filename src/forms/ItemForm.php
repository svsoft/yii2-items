<?php

namespace svsoft\yii\items\forms;

use svsoft\yii\items\entities\Field;
use svsoft\yii\items\entities\Item;
use svsoft\yii\items\entities\ItemType;
use svsoft\yii\items\helpers\PostFiles;
use yii\base\DynamicModel;

/**
 * Class ItemForm
 * @package svsoft\yii\items
 * @property-read ItemType $itemType
 */
class ItemForm extends DynamicModel
{
    /**
     * @var ItemType
     */
    protected $itemType;

    /**
     * @var Item
     */
    protected $item;

    function __construct(ItemType $itemType, array $config = [])
    {
        $this->itemType = $itemType;

        $attributes = [];
        foreach($itemType->getFields() as $field)
            $attributes[] = $field->getName();

        parent::__construct($attributes, $config);
    }

    function rules()
    {
        return $this->defaultRules();
    }

    final protected function defaultRules()
    {
        $rules = [];
        foreach($this->itemType->getFields() as $field)
        {
            $name = $field->getName();

            $rule = [];
            switch($field->getType())
            {
                case Field::TYPE_STRING :
                    $rule = [$name, 'string', 'max'=>255];
                    break;
                case Field::TYPE_TEXT :
                case Field::TYPE_HTML :
                    $rule = [$name, 'string'];
                    break;
                case Field::TYPE_INT :
                    $rule = [$name, 'integer'];
                    break;
                case Field::TYPE_REAL :
                    $rule = [$name, 'number'];
                    break;
                case Field::TYPE_FILE :
                    $rule = [$name, 'file', 'maxFiles'=>$field->getMultiple() ? 10 : 1];
                    break;
            }

            $rules[$name] = $rule;
        }

        return $rules;
    }

    /**
     * @param Item $item
     *
     * @throws \svsoft\yii\items\exceptions\ItemAttributeNotFound
     */
    function setItem(Item $item)
    {
        $this->item = $item;

        if ($item)
            (new ItemFormFiller())->fill($item, $this);
    }


    /**
     * @return Item
     */
    function getItem()
    {
        return $this->item;
    }

    /**
     * @return ItemType
     */
    function getItemType()
    {
        return $this->itemType;
    }

    /**
     * @param $data
     * @param null $formName
     *
     * @return bool
     */
    function loadFiles($data, $formName = null)
    {
        $attributes = PostFiles::getFiles($data, $formName ?: $this->formName());

        foreach($attributes as $name=>$value)
        {
            $attributes[$name] = array_merge($this->getAttribute($name), $value);
        }

        $this->setAttributes($attributes);

        return (bool)$attributes;

    }

    function getAttribute($name)
    {
        $attributes = $this->getAttributes([$name]);

        return $attributes[$name];
    }
}

