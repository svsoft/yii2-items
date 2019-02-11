<?php

namespace svsoft\yii\items\services;

use svsoft\yii\items\entities\Item;
use svsoft\yii\items\exceptions\ValidationErrorException;
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
     * @return ItemQuery
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function getQuery($itemType)
    {
        if (!$itemType instanceof ItemTypeRepository)
        {
            $itemType = $this->itemTypeRepository->getByName($itemType);
        }

        $query = new \Yii::$container->get(ItemQuery::class, [$itemType]);

        return $query;
    }

    /**
     * @return ItemRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param Item $item
     *
     * @return ItemForm
     * @throws \svsoft\yii\items\exceptions\ItemAttributeNotFound
     * @throws \svsoft\yii\items\exceptions\ItemTypeNotFoundException
     */
    public function createItemForm(Item $item)
    {
        $itemType = $this->itemTypeRepository->get($item->getItemTypeId());

        $itemForm = (new ItemFormFactory($itemType))->build();

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

}