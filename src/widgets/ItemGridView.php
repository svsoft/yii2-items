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
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    function init()
    {
        if (!$this->itemType)
            throw new InvalidCallException('Property itemType must be set');

        $additionalColumns['id'] = [
            'attribute' => 'id',
            'label' => ArrayHelper::getValue($this->labels, 'id', 'Id'),
        ];

        /** @var ItemRepository $itemRepository */
        $itemRepository = \Yii::$container->get(ItemRepository::class);

        foreach($this->itemType->getFields() as $field)
        {
            $fieldName = $field->getName();

            if ($field->getType()->getMultiple())
                continue;

            if ($field->getType()->getId() == Field::TYPE_FILE)
                continue;

            $additionalColumn = [
                    'attribute' => $field->getName(),
                    'label' => ArrayHelper::getValue($this->labels, $fieldName, Inflector::camel2words($fieldName, true)),
                ];

            if (isset($this->labels[$fieldName]))
                $additionalColumn['label'] = $this->labels[$fieldName];

            if ($field->getType()->getId() == Field::TYPE_ITEM)
            {
                $additionalColumn['value'] = function(Item $item) use ($field, $itemRepository){
                    $valueItem = $itemRepository->get($item->getAttribute($field->getName()));
                    return (string)$valueItem;
                };
            }

            $additionalColumns[$fieldName] = $additionalColumn;
        }

        $this->columns = ArrayHelper::merge($additionalColumns, $this->columns);

        parent::init();
    }

}