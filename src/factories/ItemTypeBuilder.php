<?php

namespace svsoft\yii\items\factories;

use svsoft\yii\items\entities\Field;
use svsoft\yii\items\entities\ItemType;
use svsoft\yii\items\exceptions\ItemTypeException;

class ItemTypeBuilder
{
    private $id;

    private $name;

    /**
     * @var Field[]
     */
    private $fields = [];

    private function generateId()
    {
        return uniqid(bin2hex(random_bytes(1)));
    }

    /**
     * @param $name
     *
     * @return $this
     */
    function setName($name)
    {
        $this->name = $name;
        return $this;
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
     * @param $fields
     *
     * @return $this
     */
    function setFields($fields)
    {
        $this->fields = $fields;
        return $this;
    }

    function build()
    {
        $id = $this->id ?: $this->generateId();

        if (!$this->name)
            throw new ItemTypeException('Property "name" must be set');

        $names = [];
        foreach($this->fields as $field)
        {
            if (isset($names[$field->getName()]))
                throw new ItemTypeException("Field with name \"{$field->getName()}\" already exist");
            $names[$field->getName()] = true;
        }

        $itemType = new ItemType($id, $this->name, $this->fields);

        return $itemType;
    }
}