<?php

namespace svsoft\yii\items\widgets;

use svsoft\yii\items\admin\components\Label;
use svsoft\yii\items\entities\Field;
use svsoft\yii\items\entities\Item;
use svsoft\yii\items\entities\ItemType;
use svsoft\yii\items\repositories\ItemRepository;
use yii\base\InvalidCallException;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;

class ItemGridView extends GridView
{
    /**
     * @var ItemType
     */
    public $itemType;

    /**
     * @var Label
     */
    public $itemTypeLabels;

    /**
     * @var
     */
    public $labels = [];

    /**
     * Список скрытых колонок
     *
     * @var array
     */
    public $hiddenColumns = [];

    /**
     * Список видемых колонок, если пустой массив, то выводятся все колонки соответсвубщие ItemType
     *
     * @var array
     */
    public $visibleColumns = [];


    /**
     * Возвращает список колонок для вывода
     *
     * @return array
     */
    protected function getColumnList()
    {
        $columns[] = 'id';
        foreach($this->itemType->getFields() as $field)
        {
            $columns[] = $field->getName();
        }

        foreach($columns as $key=>$columnName)
        {
            if ($this->visibleColumns && !in_array($columnName, $this->visibleColumns))
                unset($columns[$key]);

            if (in_array($columnName, $this->hiddenColumns))
                unset($columns[$key]);
        }

        return $columns;
    }

    /**
     * @throws \svsoft\yii\items\exceptions\FieldNotFoundException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    function init()
    {
        if (!$this->itemType)
            throw new InvalidCallException('Property itemType must be set');

        /** @var ItemRepository $itemRepository */
        $itemRepository = \Yii::$container->get(ItemRepository::class);

        $columnList = $this->getColumnList();

        foreach($columnList as $columnName)
        {
            $additionalColumn = [
                'attribute' => $columnName,
                'label' => ArrayHelper::getValue($this->labels, $columnName, Inflector::camel2words($columnName, true)),
            ];

            if ($columnName != 'id')
            {
                $field = $this->itemType->getFieldByName($columnName);
                $fieldName = $field->getName();

                if ($field->getType()->getMultiple())
                    continue;

                if ($field->getType()->getId() == Field::TYPE_FILE)
                    continue;

                if (isset($this->labels[$fieldName]))
                    $additionalColumn['label'] = $this->labels[$fieldName];

                if ($field->getType()->getId() == Field::TYPE_ITEM)
                {
                    $additionalColumn['value'] = function(Item $item) use ($field, $itemRepository){

                        if (!$value = $item->getAttribute($field->getName()))
                            return null;

                        $valueItem = $itemRepository->get($value);
                        return (string)$valueItem;
                    };
                }
            }

            $additionalColumns[$columnName] = $additionalColumn;
        }

        $this->columns = ArrayHelper::merge($additionalColumns, $this->columns);

        parent::init();
    }

}