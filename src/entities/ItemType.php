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
    function __construct($id, $name, $fields = [])
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

    function setName($name)
    {
        $this->name = $name;
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
            throw new FieldNotFoundException("Field with id \"{$id}\" not found");

        return $field;
    }

    function removeField($id)
    {
        $this->getField($id);

        unset($this->fields[$id]);
    }

    function addField(Field $field)
    {
        if($this->hasFieldByName($field->getName()))
            throw new FieldException('Field name "'.$field->getName().'" already exist');

        $this->fields[$field->getId()] = $field;
    }

    /**
     * @param $id
     *
     * @return bool
     */
    function hasField($id)
    {
        return isset($this->fields{$id});
    }

    /**
     * @param $name
     *
     * @return null|Field
     * @throws FieldNotFoundException
     */
    function getFieldByName($name)
    {
        $field = null;
        foreach($this->getFields() as $field)
        {
            if ($field->getName() == $name)
            {
                return $field;
            }
        }

        throw new FieldNotFoundException('Field with name "'.$name.'" not found');
    }

    /**
     * @param $name
     *
     * @return bool
     */
    function hasFieldByName($name)
    {
        try
        {
            $this->getFieldByName($name);
        }
        catch(FieldNotFoundException $exception)
        {
            return false;
        }

        return true;

    }
}