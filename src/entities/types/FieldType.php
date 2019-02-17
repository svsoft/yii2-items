<?php
namespace svsoft\yii\items\entities\types;

use yii\helpers\ArrayHelper;

class FieldType
{
    protected $id;
    protected $params;

    function __construct($id, $params)
    {
        $this->id = $id;
        $this->params = array_merge(['multiple'=>false], $params);
    }

    function getId()
    {
        return $this->id;
    }

    function getParams()
    {
        return $this->params;
    }

    function getParam($name, $default = null)
    {
        return ArrayHelper::getValue($this->params, $name, $default);
    }

    /**
     * @return mixed
     */
    function getMultiple()
    {
        return $this->params['multiple'];
    }

    /**
     * @return bool
     */
    function getRequired()
    {
        return $this->getParam('required', false);
    }

    /**
     * @return bool
     */
    function getUnique()
    {
        return $this->getParam('unique', false);
    }
}
