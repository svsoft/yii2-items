<?php

namespace svsoft\yii\items\factories;

use svsoft\yii\items\entities\Field;
use svsoft\yii\items\entities\types\FieldType;
use svsoft\yii\items\exceptions\FieldException;

class FieldBuilder
{
    private $name;

    private $id;
    /**
     * @var FieldType
     */
    private $type;

    private $multiple = false;

    private function generateId()
    {
        return uniqid(bin2hex(random_bytes(1)));
    }

    /**
     * @return $this
     */
    function multiple()
    {
        $this->multiple = true;

        return $this;
    }

    /**
     * @param FieldType $type
     *
     * @return $this
     */
    function setType(FieldType $type)
    {
        $this->type = $type;
        return $this;
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

    function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    function build()
    {
        if (empty($this->type))
            throw new FieldException('Type must be set');

        $id = $this->id ?: $this->generateId();

        return new Field($id, $this->name, $this->type);
    }

}