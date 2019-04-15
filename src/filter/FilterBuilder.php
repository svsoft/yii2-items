<?php

namespace svsoft\yii\items\filter;

use svsoft\yii\items\repositories\ItemQuery;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

class FilterBuilder
{
    public $query;

    /**
     * @var FilterAttribute[]
     */
    public $filterAttributes;

    /**
     * @param ItemQuery $query
     *
     * @return $this
     */
    function setQuery(ItemQuery $query)
    {
        $this->query = $query;
        return $this;
    }

    function setFilterAttributes($attributes)
    {
        $this->filterAttributes = $attributes;

        return $this;
    }

    /**
     * @return Filter
     * @throws InvalidConfigException
     * @throws \svsoft\yii\items\exceptions\ItemAttributeNotFound
     */
    function build()
    {
        if (empty($this->filterAttributes))
            throw new InvalidConfigException('Property "properties" must be set');

        if (!$this->query)
            throw new InvalidConfigException('Property "query" must be set');

        $attributes = [];
        foreach($this->filterAttributes as $filterAttribute)
                $attributes[] = $filterAttribute->attribute;

        $attributeValues = $this->getAttributeValues($this->query, $attributes);

        foreach($this->filterAttributes as $filterAttribute)
        {
            $attribute = $filterAttribute->attribute;
            $values = ArrayHelper::getValue($attributeValues, $attribute, []);
            if ($filterAttribute instanceof FilterAttributeList)
            {
                $filterAttribute->values = ArrayHelper::getValue($attributeValues, $attribute, []);
            }
            elseif ($filterAttribute instanceof FilterAttributeRange)
            {
                if ($values)
                {
                    $filterAttribute->from = min($values);
                    $filterAttribute->to = max($values);
                }
            }
        }

        return new Filter($this->filterAttributes);
    }

    /**
     * @param ItemQuery $query
     * @param $attributes
     *
     * @return array
     * @throws \svsoft\yii\items\exceptions\ItemAttributeNotFound
     */
    protected function getAttributeValues(ItemQuery $query, $attributes)
    {
        $items = $query->all();

        $attributeValues = [];
        foreach($items as $item)
        {
            foreach($attributes as $attribute)
            {
                $attributeValue = $item->getAttribute($attribute);

                if (\is_array($attributeValue))
                {
                    foreach($attributeValue as $value)
                    {
                        $attributeValues[$attribute][$value] = $value;
                    }
                }
                elseif ($attributeValue !== null)
                {
                    $attributeValues[$attribute][$attributeValue] = $attributeValue;
                }
            }
        }

        foreach($attributes as $attribute)
        {
            if (empty($attributeValues[$attribute]))
                $attributeValues[$attribute] = [];
        }

        return $attributeValues;
    }

}