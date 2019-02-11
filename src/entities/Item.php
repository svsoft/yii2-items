<?php

namespace svsoft\yii\items\entities;

use svsoft\yii\items\exceptions\ItemAttributeNotFound;

class Item
{
    protected $id;
    protected $itemTypeId;
    protected $attributes;

    function __construct($id, $itemTypeId, $attributes)
    {
        $this->id = $id;
        $this->itemTypeId = $itemTypeId;
        $this->attributes = $attributes;
    }

    function getId()
    {
        return $this->id;
    }

    function getItemTypeId()
    {
        return $this->itemTypeId;
    }

    /**
     * @param $name
     *
     * @return mixed|FileAttributeInterface|FileAttributeInterface[]
     * @throws ItemAttributeNotFound
     */
    function getAttribute($name)
    {
        if (!$this->hasAttribute($name))
            throw new ItemAttributeNotFound('Item attribute not found');

        return $this->attributes[$name];
    }

    /**
     * @param $name
     * @param $value
     *
     * @throws ItemAttributeNotFound
     */
    function setAttribute($name, $value)
    {
        if (!$this->hasAttribute($name))
            throw new ItemAttributeNotFound('Item attribute not found');

        $this->attributes[$name] = $value;
    }

    function getAttributes()
    {
        return $this->attributes;
    }

    function setAttributes($attributes)
    {
        foreach($attributes as $name=>$value)
        {
            $this->setAttribute($name, $value);
        }
    }

    function hasAttribute($name)
    {
        return array_key_exists($name, $this->attributes);
    }

    function getProperty($name)
    {
        if ($name == 'id')
            return $this->id;

        return $this->getAttribute($name);
    }

    function __get($name)
    {
        if ($name == 'id')
            return $this->getId();

        return $this->getAttribute($name);
    }
}