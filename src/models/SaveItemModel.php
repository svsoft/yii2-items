<?php

namespace svsoft\yii\items\models;

use svsoft\yii\items\entities\Item;
use svsoft\yii\items\entities\ItemType;
use svsoft\yii\items\exceptions\ValidationErrorException;
use svsoft\yii\items\factories\ItemBuilder;
use svsoft\yii\items\forms\ItemFiller;
use svsoft\yii\items\forms\ItemForm;
use svsoft\yii\items\services\ItemManager;

/**
 * Class ItemForm
 * @package svsoft\yii\items
 * @property-read ItemType $itemType
 */
class SaveItemModel extends ItemForm
{

    function isNewItem()
    {
        return !(bool)$this->item;
    }

    /**
     * @throws \Throwable
     * @throws \svsoft\yii\items\exceptions\ItemAttributeNotFound
     * @throws \svsoft\yii\items\exceptions\ItemException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    function save()
    {
        /** @var ItemManager $itemManager */
        $itemManager = \Yii::$container->get(ItemManager::class);

        if (!$this->validate())
            throw new ValidationErrorException('invalid model');

        $this->beforeSave();

        if ($this->item)
        {
            $item = $this->item;
        }
        else
        {
            /** @var ItemBuilder $builder */
            $builder = \Yii::createObject(ItemBuilder::class);
            $item = $builder->setItemType($this->itemType)->build();
        }
        (new ItemFiller())->fill($item, $this);

        if ($this->isNewItem())
            $itemManager->create($item);
        else
            $itemManager->update($item);

        $this->afterSave($item);
    }

    protected function beforeSave()
    {

    }

    protected function afterSave(Item $item)
    {
    }
}

