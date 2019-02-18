<?php

namespace svsoft\yii\items\factories;

use svsoft\yii\items\entities\Item;
use svsoft\yii\items\entities\ItemType;
use svsoft\yii\items\exceptions\ItemException;

class ItemBuilder
{
    protected $attributes = [];

    protected $id;

    /**
     * @var ItemType
     */
    protected $itemType;

    /**
     * @var
     */
    protected $itemFactory;

    function __construct(ItemFactory $itemFactory)
    {
        $this->itemFactory = $itemFactory;
    }

    private function generateId()
    {
        return uniqid(bin2hex(random_bytes(1)));
    }

    /**
     * @param $id
     *
     * @return $this
     */
    function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param $attributes
     *
     * @return $this
     */
    function setAttributes($attributes)
    {
        $this->attributes = $attributes;
        return $this;
    }


    /**
     * @param ItemType $itemType
     *
     * @return $this
     */
    function setItemType(ItemType $itemType)
    {
        $this->itemType = $itemType;
        return $this;
    }

    function build()
    {
        if (!$this->id)
            $this->id = $this->generateId();

        if (!$this->itemType)
            throw new ItemException('Item type must be set');

        if (empty($this->attributes))
        {
            foreach($this->itemType->getFields() as $field)
            {
                $this->attributes[$field->getName()] = $field->getMultiple() ? [] : null;
            }
        }

        return new Item($this->id, $this->itemType->getId(), $this->attributes);
    }

}