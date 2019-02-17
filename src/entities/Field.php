<?php

namespace svsoft\yii\items\entities;

use svsoft\yii\items\entities\types\FieldType;

class Field
{
    const TYPE_STRING = 'string';
    const TYPE_INT    = 'int';
    const TYPE_REAL   = 'real';
    const TYPE_TEXT   = 'text';
    const TYPE_FILE   = 'file';
    const TYPE_HTML   = 'html';
    const TYPE_ITEM   = 'item';

    protected $id;
    protected $name;
    /**
     * @var FieldType
     */
    protected $type;

    static function types()
    {
        return [self::TYPE_STRING, self::TYPE_INT, self::TYPE_REAL, self::TYPE_TEXT, self::TYPE_STRING, self::TYPE_HTML, self::TYPE_ITEM];
    }

    function __construct($id, $name, FieldType $type)
    {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
    }

    function getMultiple()
    {
        return $this->type->getMultiple();
    }

    /**
     * @return FieldType
     */
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

    function setType(FieldType $type)
    {
        $this->type = $type;
    }
}