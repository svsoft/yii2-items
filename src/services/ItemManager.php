<?php

namespace svsoft\yii\items\services;

use svsoft\yii\items\entities\Item;
use svsoft\yii\items\entities\ItemType;
use svsoft\yii\items\exceptions\ValidationErrorException;
use svsoft\yii\items\factories\ItemBuilder;
use svsoft\yii\items\factories\ItemFormFactory;
use svsoft\yii\items\forms\ItemFiller;
use svsoft\yii\items\forms\ItemForm;
use svsoft\yii\items\repositories\ItemQuery;
use svsoft\yii\items\repositories\ItemRepository;
use svsoft\yii\items\repositories\ItemTypeRepository;
use yii\base\Component;

/**
 * Class ItemService
 * @package svsoft\yii\items\services
 */
class ItemManager extends Component
{
    /**
     * @var ItemRepository
     */
    protected $itemRepository;

    /**
     * @var ItemTypeRepository
     */
    protected $itemTypeRepository;

    /** @var Cacher */
    protected $cacher;

    /**
     * @return object|ItemRepository
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    protected function getItemRepository()
    {
        return \Yii::$container->get(ItemRepository::class);
    }

    /**
     * @return object|ItemTypeRepository
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    protected function getItemTypeRepository()
    {
        return \Yii::$container->get(ItemTypeRepository::class);
    }

    /**
     * @return Cacher|object
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    protected function getCacher()
    {
        return \Yii::$container->get(Cacher::class);
    }

    /**
     * @param $name
     *
     * @return ItemType
     * @throws \svsoft\yii\items\exceptions\ItemTypeNotFoundException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    protected function getItemTypeByName($name)
    {
        return $this->getItemTypeRepository()->getByName($name);
    }


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
            $itemType = $this->getItemTypeByName($itemType);

        /** @var ItemQuery $query */
        $query = \Yii::createObject(ItemQuery::class, [$itemType]);
        return $query;
    }


    /**
     * @param $itemType
     *
     * @return ItemForm
     * @throws \svsoft\yii\items\exceptions\ItemTypeNotFoundException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function createForm($itemType)
    {
        if (!$itemType instanceof ItemType)
            $itemType = $this->getItemTypeByName($itemType);

        /** @var ItemFormFactory $factory */
        $factory = \Yii::$container->get(ItemFormFactory::class);

        return $factory->build($itemType);
    }

    public function update(ItemForm $itemForm)
    {
        if (!$itemForm->validate())
            throw new ValidationErrorException('Item validation error');

        $item = $itemForm->getItem();

        (new ItemFiller())->fill($item, $itemForm);

        $this->getItemRepository()->update($item);

        $this->getCacher()->cleanByItemType($item->getItemTypeId());
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

        /** @var ItemBuilder $builder */
        $builder = \Yii::createObject(ItemBuilder::class);

        $item = $builder->setItemType($itemForm->itemType)->build();

        (new ItemFiller())->fill($item, $itemForm);

        $this->getItemRepository()->create($item);

        $this->getCacher()->cleanByItemType($item->getItemTypeId());

        return $item;
    }

    /**
     * @param Item $item
     *
     * @throws \Throwable
     */
    public function delete(Item $item)
    {
        $this->getItemRepository()->delete($item);
    }
}