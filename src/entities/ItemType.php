<?php

namespace svsoft\yii\items\entities;

use svsoft\yii\items\exceptions\FieldException;
use svsoft\yii\items\exceptions\FieldNotFoundException;
use yii\helpers\ArrayHelper;

class ItemType
{
    protected $id;

    protected $name;

    /**
     * @var Field[]
     */
    protected $fields = [];

    /**
     * ItemType constructor.
     *
     * @param $id
     * @param $name
     * @param Field[] $fields
     */
    function __construct($id, $name, $fields)
    {
        $this->id = $id;
        $this->name = $name;

        foreach($fields as $field)
            $this->fields[$field->getId()] = $field;
    }

    function getId()
    {
        return $this->id;
    }

    function getName()
    {
        return $this->name;
    }

    /**
     * @return Field[]
     */
    function getFields()
    {
        return $this->fields;
    }

    /**
     * @param $id
     *
     * @return Field
     * @throws FieldNotFoundException
     */
    function getField($id)
    {
        if (!$field = ArrayHelper::getValue($this->fields, $id))
            throw new FieldNotFoundException();

        return $field;
    }

    function removeField($id)
    {
        $this->getField($id);

        unset($this->fields[$id]);
    }

    function addField(Field $field)
    {
        foreach($this->fields as $fieldItem)
            if ($fieldItem->getName() == $field->getName())
                throw new FieldException('Field name "'.$field->getName().'" already exist');

        $this->fields[$field->getId()] = $field;
    }

    function hasField($name)
    {
        return isset($this->fields{$name});
    }
}