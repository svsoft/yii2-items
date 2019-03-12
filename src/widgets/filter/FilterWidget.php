<?php
namespace svsoft\yii\items\widgets\filter;

use svsoft\yii\items\filter\Filter;
use svsoft\yii\items\filter\FilterForm;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;

class FilterWidget extends Widget
{
    /**
     * @var Filter
     */
    public $filter;

    /**
     * @var FilterForm
     */
    public $filterForm;

    public $labels = [];

    function init()
    {
        $this->labels = ArrayHelper::merge($this->defaultLabels(), $this->labels);
        parent::init();
    }

    function defaultLabels()
    {
        $properties = array_keys($this->filter->getProperties());

        $labels = [];
        foreach($properties as $name)
        {
            $labels[$name] = Inflector::camel2words($name, true);
        }

        return $labels;
    }

    function getLabel($name)
    {
        return ArrayHelper::getValue($this->labels, $name);
    }
}
