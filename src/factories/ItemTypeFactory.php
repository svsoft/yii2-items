<?php

namespace svsoft\yii\items\factories;

use svsoft\yii\items\entities\Field;
use svsoft\yii\items\entities\ItemType;

class ItemTypeFactory
{
    private $name;

    /**
     * @var Field[]
     */
    private $fields = [];

    private function generateId()
    {
        return uniqid(bin2hex(random_bytes(1)));
    }

    function __construct($name)
    {
        $this->name = $name;
    }

    function build()
    {
        $itemType = new ItemType($this->generateId(), $this->name);

        foreach($this->fields as $field)
            $itemType->addField($field);

        return new ItemType($this->generateId(), $this->name, $this->fields);
    }

}