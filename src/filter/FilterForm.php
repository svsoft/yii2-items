<?php
namespace svsoft\yii\items\filter;

use yii\base\DynamicModel;
use yii\helpers\ArrayHelper;

class FilterForm extends DynamicModel
{
    public $formName;

    function formName()
    {
        if ($this->formName !== null)
            return $this->formName;

        return 'f';
    }

    function getAttribute($name)
    {
        $values = parent::getAttributes([$name]);
        return ArrayHelper::getValue($values, $name);
    }
}