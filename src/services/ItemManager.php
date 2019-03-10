<?php

namespace svsoft\yii\items\services;

use svsoft\yii\items\entities\Item;
use svsoft\yii\items\entities\ItemType;
use svsoft\yii\items\factories\SaveModelFactory;
use svsoft\yii\items\models\SaveItemModel;
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
     * @return SaveItemModel
     * @throws \svsoft\yii\items\exceptions\ItemTypeNotFoundException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function createSaveModel($itemType)
    {
        if (!$itemType instanceof ItemType)
            $itemType = $this->getItemTypeByName($itemType);

        /** @var SaveModelFactory $factory */
        $factory = \Yii::$container->get(SaveModelFactory::class);

        return $factory->build($itemType);
    }

    /**
     * @param Item $item
     *
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function update(Item $item)
    {
        $this->getItemRepository()->update($item);

        $this->getCacher()->cleanByItemType($item->getItemTypeId());
    }

    /**
     * @param Item $item
     *
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function create(Item $item)
    {
        $this->getItemRepository()->create($item);

        $this->getCacher()->cleanByItemType($item->getItemTypeId());
    }

    /**
     * @param Item $item
     *
     * @throws \Throwable
     */
    public function delete(Item $item)
    {
        $this->getItemRepository()->delete($item);

        $this->getCacher()->cleanByItemType($item->getItemTypeId());
    }
}