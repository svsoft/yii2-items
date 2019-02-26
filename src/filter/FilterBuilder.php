<?php

namespace svsoft\yii\items\filter;

use svsoft\yii\items\entities\ItemType;
use svsoft\yii\items\repositories\ItemQuery;
use svsoft\yii\items\repositories\ItemTypeRepository;
use yii\base\InvalidConfigException;
use yii\db\Query;

class FilterBuilder
{
    protected $properties;

    /**
     * @var callable
     */
    protected $queryFilter;

    /**
     * @var ItemType
     */
    protected $itemType;

    /**
     * @var ItemQuery
     */
    protected $query;

    /**
     * @param $properties
     *
     * @return $this
     */
    public function setProperties($properties)
    {
        $propertyData = [];
        foreach($properties as $key=>$property)
        {
            if (is_numeric($key))
            {
                $propertyName = $property;
                $propertyData['type'] = FilterProperty::FILTER_TYPE_VALUE;
            }
            else
            {
                $propertyName = $key;
                $propertyData['type'] = $property;
            }

            $this->properties[$propertyName] = $propertyData;
        }

        return $this;
    }

    /**
     * @param string|ItemType $itemType
     *
     * @return $this
     */
    public function setItemType($itemType)
    {
        /** @var ItemTypeRepository $repository */
        $repository = \Yii::$container->get(ItemTypeRepository::class);

        if (!$itemType instanceof ItemType)
            $itemType = $repository->getByName($itemType);

        $this->itemType = $itemType;

        return $this;
    }

    /**
     * @param callable $callback
     *
     * @return $this
     */
    public function setQueryFilter(callable $callback)
    {
        $this->queryFilter = $callback;

        return $this;
    }

    public function setQuery(Query $query)
    {
        $this->query = $query;

        return $this;
    }

    public function build()
    {
        if (empty($this->properties))
            throw new InvalidConfigException('Property "properties" must be set');


        if (!$this->query)
        {
            if (!$this->itemType)
                throw new InvalidConfigException('property "itemType" must be set');

            $this->query = \Yii::createObject(ItemQuery::class, [$this->itemType]);
        }

        if ($this->queryFilter)
            call_user_func($this->queryFilter, $this->query);

        $items = $this->query->all();

        $properties = $this->properties;
        $filterProperties = [];
        foreach($properties as $propertyName=>$propertyData)
        {
            // $field = $this->itemType->getFieldByName($propertyName);
            $propertyValues = [];
            foreach($items as $item)
            {
                $attributeValue = $item->getAttribute($propertyName);

                if (is_array($attributeValue))
                {
                    foreach($attributeValue as $value)
                    {
                        $propertyValues[$value] = $value;
                    }
                }
                elseif ($attributeValue !== null)
                {
                    $propertyValues[$attributeValue] = $attributeValue;
                }
            }

            $filterProperties[] = new FilterProperty(['values' => $propertyValues,'name' => $propertyName, 'type' => $propertyData['type']]);
        }

        return new Filter($filterProperties);
    }
}
