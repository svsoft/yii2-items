<?php

namespace svsoft\yii\items\filter;

use svsoft\yii\items\exceptions\ValidationErrorException;
use svsoft\yii\items\repositories\ItemQuery;
use yii\helpers\ArrayHelper;

class FilterManager
{

    /**
     * @param ItemQuery $query
     * @param $filterAttributes
     *
     * @return FilterBuilder
     */
    function filterBuilder(ItemQuery $query, $filterAttributes)
    {
        return (new FilterBuilder())
            ->setQuery($query)
            ->setFilterAttributes($filterAttributes);
    }

    /**
     * @param ItemQuery $query
     * @param $filterAttributes
     *
     * @return Filter
     * @throws \svsoft\yii\items\exceptions\ItemAttributeNotFound
     * @throws \yii\base\InvalidConfigException
     */
    function createFilter(ItemQuery $query, $filterAttributes)
    {
        return $this->filterBuilder($query, $filterAttributes)->build();
    }

    /**
     * @param ItemQuery $query
     * @param Filter $filter
     *
     * @return ItemQuery
     * @throws ValidationErrorException
     */
    function filter(ItemQuery $query, Filter $filter)
    {
        if (!$filter->validate())
            throw new ValidationErrorException('Validation error');

        foreach($filter->getFilterAttributes() as $formAttribute=>$filterAttribute)
        {
            $attribute = $filterAttribute->attribute;

            $attributeValue  = $filter->$formAttribute;
            if ($attributeValue === '' || $attributeValue === null)
                continue;


            if ($filterAttribute instanceof FilterAttributeMore)
            {
                $query->andFilterWhere([$filterAttribute->strict ? '>' : '>=', $attribute, $attributeValue]);
            }
            elseif ($filterAttribute instanceof FilterAttributeLess)
            {
                $query->andFilterWhere([$filterAttribute->strict ? '<' : '<=', $attribute, $attributeValue]);
            }
            elseif ($filterAttribute instanceof FilterAttributeBetween)
            {
                $ar = explode(',',$attributeValue);
                $from = ArrayHelper::getValue($ar, 0);
                $to = ArrayHelper::getValue($ar, 1);

                $query->andWhere(['BETWEEN', $attribute, $from, $to]);
            }
            elseif ($filterAttribute instanceof FilterAttributeList)
            {
                $query->andFilterWhere([$attribute=>$attributeValue]);
            }
            else
            {
                $query->andFilterWhere([$attribute=>$attributeValue]);
            }
        }

        return $query;
    }

}