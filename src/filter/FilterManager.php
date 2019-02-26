<?php

namespace svsoft\yii\items\filter;

use svsoft\yii\items\repositories\ItemQuery;

class FilterManager
{
    function createFilterBuilder()
    {
        return new FilterBuilder();
    }


    function createFilter(ItemQuery $query, $properties)
    {
        return ( new FilterBuilder())->setProperties($properties)->setQuery($query)->build();
    }

    /**
     * @param Filter $filter
     *
     * @return FilterForm
     */
    function createForm(Filter $filter)
    {
        $attributes = [];
        foreach($filter->getProperties() as $property)
            $attributes[] = $property->name;

        $form = new FilterForm($attributes);

        foreach($filter->getProperties() as $property)
        {
            $attribute = $property->name;
            if ($property->type == FilterProperty::FILTER_TYPE_RANGE)
            {
                $form->$attribute = [$property->getMinValue(), $property->getMaxValue()];
            }
            $form->addRule($attribute, 'safe');
        }


        return $form;
    }

    function filter(ItemQuery $query, FilterForm $filterForm, Filter $filter)
    {
        if (!$filterForm->validate())
            return;

        foreach($filter->getProperties() as $filterProperty)
        {
            $attribute = $filterProperty->name;

            $attributeValue  = $filterForm->$attribute;
            if ($attributeValue === '' || $attributeValue === null)
                continue;

            switch($filterProperty->type)
            {
                case FilterProperty::FILTER_TYPE_RANGE:
                    if ($attributeValue[0] && $attributeValue[1])
                        $query->andWhere(['BETWEEN', $attribute, $attributeValue[0], $attributeValue[1]]);
                    break;
                default:
                    $query->andWhere([$attribute => $attributeValue]);
            }
        }
    }
}