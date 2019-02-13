<?php

namespace svsoft\yii\items\services;

use svsoft\yii\items\entities\Item;
use svsoft\yii\items\entities\ItemType;
use svsoft\yii\items\exceptions\ValidationErrorException;
use svsoft\yii\items\factories\ItemFactory;
use svsoft\yii\items\factories\ItemFormFactory;
use svsoft\yii\items\forms\ItemFiller;
use svsoft\yii\items\forms\ItemFormFiller;
use svsoft\yii\items\forms\ItemForm;
use svsoft\yii\items\repositories\ItemQuery;
use svsoft\yii\items\repositories\ItemRepository;
use svsoft\yii\items\repositories\ItemTypeRepository;
use yii\base\BaseObject;

/**
 * Class ItemService
 * @package svsoft\yii\items\services
 */
class ItemService extends BaseObject
{

    /**
     * @var ItemRepository
     */
    protected $repository;

    /**
     * @var ItemTypeRepository
     */
    protected $itemTypeRepository;

    public function __construct(ItemRepository $itemRepository, ItemTypeRepository $itemTypeRepository)
    {
        $this->repository = $itemRepository;
        $this->itemTypeRepository = $itemTypeRepository;

        parent::__construct([]);
    }

    /**
     * @param $itemType
     *
     * @return ItemQuery|string
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function getQuery($itemType)
    {
        if (!$itemType instanceof ItemType)
        {
            $itemType = $this->itemTypeRepository->getByName($itemType);
        }

        $query = \Yii::createObject(ItemQuery::class, [$itemType]);

        return $query;
    }

    /**
     * @param $name
     *
     * @return \svsoft\yii\items\entities\ItemType
     * @throws \svsoft\yii\items\exceptions\ItemTypeNotFoundException
     */
    public function getItemType($name)
    {
        return $this->itemTypeRepository->getByName($name);
    }

    /**
     * @param $id
     *
     * @return ItemType
     * @throws \svsoft\yii\items\exceptions\ItemTypeNotFoundException
     */
    public function getItemTypeById($id)
    {
        return $this->itemTypeRepository->get($id);
    }

    /**
     * @return ItemRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param ItemType $itemType
     * @param Item|null $item
     *
     * @return ItemForm
     * @throws \svsoft\yii\items\exceptions\ItemAttributeNotFound
     */
    public function createItemForm(ItemType $itemType, Item $item = null)
    {
        $itemForm = (new ItemFormFactory($itemType))->build();

        if ($item)
            (new ItemFormFiller())->fill($item, $itemForm);

        return $itemForm;
    }

    public function update(Item $item, ItemForm $itemForm)
    {
        if (!$itemForm->validate())
            throw new ValidationErrorException('Item validation error');

        (new ItemFiller())->fill($item, $itemForm);

        // var_dump($item->img);die();

        $this->repository->update($item);
    }

    /**
     * @param ItemForm $itemForm
     *
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