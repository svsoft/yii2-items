<?php

namespace svsoft\yii\items\factories;

use svsoft\yii\items\entities\Field;
use svsoft\yii\items\exceptions\FieldException;

class FieldFactory
{
    private $name;
    private $type;
    private $multiple = false;

    private function generateId()
    {
        return uniqid(bin2hex(random_bytes(1)));
    }

    function __construct($name, $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    function multiple()
    {
        $this->multiple = true;

        return $this;
    }

    /**
     * @return Field
     * @throws FieldException
     */
    function build()
    {
        if (empty($this->type))
            throw new FieldException('Type must be set');

        return new Field($this->generateId(), $this->name, $this->type);
    }

}