<?php

namespace svsoft\yii\items\entities;

use svsoft\yii\items\exceptions\FieldException;

class Field
{
    const TYPE_STRING = 'string';
    const TYPE_INT    = 'int';
    const TYPE_REAL   = 'real';
    const TYPE_TEXT   = 'text';
    const TYPE_FILE   = 'file';

    protected $id;
    protected $name;
    protected $type;
    protected $multiple;

    static function types()
    {
        return [self::TYPE_STRING, self::TYPE_INT, self::TYPE_REAL, self::TYPE_TEXT, self::TYPE_STRING];
    }

    function __construct($id, $name, $type, $multiple)
    {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->multiple = $multiple;
    }

    function getMultiple()
    {
        return $this->multiple;
    }

    function getType()
    {
        return $this->type;
    }

    function getId()
    {
        return $this->id;
    }

    function getName()
    {
        return $this->name;
    }

    function setName($name)
    {
        $this->name = $name;
    }

    function setMultiple($multiple)
    {
        $this->multiple = (bool)$multiple;
    }

    function setType($type)
    {
        if (!in_array($type, self::types()))
            throw new FieldException('Type "'.$type.'" not found');

        $this->type = $type;
    }
}