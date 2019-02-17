<?php

namespace svsoft\yii\items\services;

use svsoft\yii\items\entities\Item;
use svsoft\yii\items\entities\ItemType;
use svsoft\yii\items\exceptions\ValidationErrorException;
use svsoft\yii\items\factories\ItemFactory;
use svsoft\yii\items\forms\ItemFiller;
use svsoft\yii\items\forms\ItemForm;
use svsoft\yii\items\repositories\ItemQuery;
use svsoft\yii\items\repositories\ItemRepository;
use yii\base\Component;

/**
 * Class ItemService
 * @package svsoft\yii\items\services
 * @property-read ItemRepository $repository
 */
class ItemManager extends Component
{
    /**
     * @var Items
     */
    public $items;

    /**
     * @param $itemType
     *
     * @return ItemQuery
     * @throws \svsoft\yii\items\exceptions\ItemTypeNotFoundException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function createQuery($itemType)
    {
        if (!$itemType instanceof ItemType)
        {
            $itemType = $this->items->getItemTypeRepository()->getByName($itemType);
        }

        /** @var ItemQuery $query */
        $query = \Yii::createObject(ItemQuery::class, [$itemType]);
        return $query;
    }

    /**
     * @return ItemRepository
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    protected function getRepository()
    {
        return $this->items->getItemRepository();
    }

    public function createForm($itemType)
    {
        if (!$itemType instanceof ItemType)
            $itemType = $this->items->getItemTypeRepository()->getByName($itemType);

        return $this->items->getItemFormFactory()->build($itemType);
    }

    public function update(ItemForm $itemForm)
    {
        if (!$itemForm->validate())
            throw new ValidationErrorException('Item validation error');

        $item = $itemForm->getItem();

        (new ItemFiller())->fill($item, $itemForm);

        $this->repository->update($item);
    }

    /**
     * @param ItemForm $itemForm
     *
     * @return Item
     * @throws ValidationErrorException
     * @throws \Throwable
     * @throws \svsoft\yii\items\exceptions\ItemAttributeNotFound
     */
    public function create(ItemForm $itemForm)
    {
        if (!$itemForm->validate())
            throw new ValidationErrorException('Item validation error');

        $item = (new ItemFactory($itemForm->itemType))->build();

        (new ItemFiller())->fill($item, $itemForm);

        $this->repository->create($item);

        return $item;
    }

    /**
     * @param Item $item
     *
     * @throws \Throwable
     */
    public function delete(Item $item)
    {
        $this->repository->delete($item);
    }
}