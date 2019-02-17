<?php
namespace svsoft\yii\items\factories;

class FieldTypeBuilder
{
    protected $id;

    protected $params = [];
    /**
     * @var FieldTypeFactory
     */
    protected $fieldTypeFactory;

    function __construct(FieldTypeFactory $fieldTypeFactory)
    {
        $this->fieldTypeFactory = $fieldTypeFactory;
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
     * @return $this
     */
    function multiple()
    {
        return $this->setParam('multiple', true);
    }

    /**
     * @return $this
     */
    function required()
    {
        return $this->setParam('required', true);
    }

    /**
     * @return FieldTypeBuilder
     */
    function unique()
    {
        return $this->setParam('unique', true);
    }

    function setParam($name, $value)
    {
        $this->params[$name] = $value;
        return $this;
    }

    function build()
    {
        return $this->fieldTypeFactory->build($this->id, $this->params);
    }
}