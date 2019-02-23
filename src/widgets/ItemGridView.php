<?php

namespace svsoft\yii\items\widgets;

use svsoft\yii\items\entities\Field;
use svsoft\yii\items\entities\Item;
use svsoft\yii\items\entities\ItemType;
use svsoft\yii\items\repositories\ItemRepository;
use yii\base\InvalidCallException;
use yii\grid\GridView;

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

        $additionalColumns[] = 'id';

        /** @var ItemRepository $itemRepository */
        $itemRepository = \Yii::$container->get(ItemRepository::class);

        foreach($this->itemType->getFields() as $field)
        {
            if ($field->getType()->getMultiple())
                continue;

            if ($field->getType()->getId() == Field::TYPE_ITEM)
            {

                $additionalColumns[] = [
                    'attribute'=>$field->getName(),
                    'value' => function(Item $item) use ($field, $itemRepository){
                        $valueItem = $itemRepository->get($item->getAttribute($field->getName()));
                        return (string)$valueItem;
                }
                ];

                continue;
            }

            if ($field->getType()->getId() == Field::TYPE_FILE)
                continue;

            $additionalColumns[] = $field->getName();
        }

        $this->columns = array_merge($additionalColumns, $this->columns);

        parent::init();
    }

}