<?php
namespace svsoft\yii\items\filter;

use yii\base\DynamicModel;
use yii\base\Exception;
use yii\helpers\ArrayHelper;

class Filter extends DynamicModel
{

    protected $filterAttributes;

    /**
     * Filter constructor.
     *
     * @param FilterAttribute[] $filterAttributes
     * @param array $config
     */
    function __construct($filterAttributes, array $config = [])
    {
        $attributes = [];
        foreach($filterAttributes as $key=>$filterAttribute)
        {
            if (is_numeric($key))
                $formAttribute = $filterAttribute->attribute;
            else
                $formAttribute = $key;

            $this->filterAttributes[$formAttribute] = $filterAttribute;

            $attributes[] =  $formAttribute;
        }

        parent::__construct($attributes, $config);
    }

    function init()
    {

        foreach($this->getFilterAttributes() as $attribute=>$filterAttribute)
        {
            if ($filterAttribute instanceof FilterAttributeList)
            {
                if ($filterAttribute->multiple)
                    $this->$attribute = [];

                $this->addRule([$attribute], 'in',['range'=>$filterAttribute->values]);
            }
            elseif ($filterAttribute instanceof FilterAttributeLess || $filterAttribute instanceof FilterAttributeMore)
                $this->addRule([$attribute], 'number');
            else
                $this->addRule([$attribute], 'safe');
        }

        parent::init();
    }

    function beforeValidate()
    {
        foreach($this->getFilterAttributes() as $attribute=>$filterAttribute)
        {
            if ($filterAttribute instanceof FilterAttributeLess || $filterAttribute instanceof FilterAttributeMore)
                $this->$attribute = str_replace(',', '.', $this->$attribute);
        }

        return parent::beforeValidate();
    }


    /**
     * @param string $attribute
     *
     * @return string
     * @throws Exception
     */
    function getAttributeLabel($attribute)
    {
        return $this->getFilterAttribute($attribute)->label;
    }

    function getAttribute($attribute)
    {
        return $this->$attribute;
    }

    /**
     * @param $attribute
     *
     * @return FilterAttribute
     * @throws Exception
     */
    function getFilterAttribute($attribute)
    {
        $filterAttribute = ArrayHelper::getValue($this->filterAttributes, $attribute);

        if (!$filterAttribute)
            throw new Exception('Filter "'.$attribute.'" attribute not found');

        return $filterAttribute;
    }

    /**
     * @return FilterAttribute[]
     */
    function getFilterAttributes()
    {
        return $this->filterAttributes;
    }

    function formName()
    {
        return 'f';
    }
}