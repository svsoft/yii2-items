<?php

namespace svsoft\yii\items\forms;

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

    function __construct(array $attributes = [], ItemType $itemType, array $config = [])
    {
        $this->itemType = $itemType;
        parent::__construct($attributes, $config);
    }

//    function rules()
//    {
//        return [
//            ['img','file', 'maxFiles' => ]
//        ];
//    }

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

