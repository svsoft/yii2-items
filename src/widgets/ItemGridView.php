<?php

namespace svsoft\yii\items\widgets;

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
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    function init()
    {
        if (!$this->itemType)
            throw new InvalidCallException('Property itemType must be set');

        $additionalColumns['id'] = [
            'attribute' => 'id',
            'label' => \Yii::t('items','Id'),
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
                    'label' => \Yii::t('items', Inflector::camel2words($field->getName(), true)),
                ];

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