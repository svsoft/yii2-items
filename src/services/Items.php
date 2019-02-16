<?php
/**
 * Created by PhpStorm.
 * User: viktor
 * Date: 14.02.2019
 * Time: 11:17
 */

namespace svsoft\yii\items\services;

use svsoft\yii\items\entities\ItemType;
use svsoft\yii\items\factories\ItemFormFactory;
use svsoft\yii\items\repositories\FileStorage;
use svsoft\yii\items\repositories\hydrators\ItemHydrator;
use svsoft\yii\items\repositories\hydrators\ItemTypeHydrator;
use svsoft\yii\items\repositories\ItemQuery;
use svsoft\yii\items\repositories\ItemRepository;
use svsoft\yii\items\repositories\ItemTypeRepository;
use svsoft\yii\items\repositories\TableManager;
use yii\base\InvalidConfigException;
use yii\db\Connection;
use yii\di\ServiceLocator;

/**
 * Class Items
 * @package svsoft\yii\items\services
 * @property ItemManager $itemManager
 */
class Items extends ServiceLocator
{
    /**
     * @var Connection
     */
    public $db;

    /**
     * @var string
     */
    public $storagePath;

    /**
     * Соответсвие типов элементов и классов форм
     * @var array
     */
    public $formClasses = [];

    /**
     * @var FileStorage
     */
    protected $_fileStorage;

    /**
     * @var ItemFormFactory
     */
    protected $_itemFormFactory;

    /**
     *
     * @return ItemRepository
     * @throws InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function getItemRepository()
    {
        /** @var ItemRepository $repo */
        $repo = \Yii::$container->get(ItemRepository::class);
        return $repo;
    }

    /**
     * @return ItemTypeRepository
     * @throws InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function getItemTypeRepository()
    {
        /** @var ItemTypeRepository $repo */
        $repo = \Yii::$container->get(ItemTypeRepository::class);
        return $repo;
    }

    /**
     * @return null|object|ItemFormFactory
     * @throws InvalidConfigException
     */
    public function getItemFormFactory()
    {
        if ($this->_itemFormFactory === null)
        {
            $this->_itemFormFactory = \Yii::createObject(ItemFormFactory::class, [$this->formClasses]);
        }

        return $this->_itemFormFactory;
    }

    /**
     * @param ItemType|string $itemType
     *
     * @return ItemQuery|string
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function createQuery($itemType)
    {
        if (!$itemType instanceof ItemType)
        {
            $itemType = $this->getItemTypeRepository()->getByName($itemType);
        }

        $query = \Yii::createObject(ItemQuery::class, [$itemType]);

        return $query;
    }

    /**
     * @return FileStorage
     */
    public function getFileStorage()
    {
        return $this->_fileStorage;
    }

    public function init()
    {
        if (!$this->storagePath)
            throw new InvalidConfigException('Property storagePath must be set');

        $this->storagePath = \Yii::getAlias($this->storagePath);

        $container = \Yii::$container;

        $fileStorage = $container->get(FileStorage::class,[$this->storagePath]);
        $this->_fileStorage = $fileStorage;

        $container->setSingleton(TableManager::class, [], [\Yii::$app->db]);
        $container->setSingleton(ItemTypeRepository::class, [], [$fileStorage]);
        $container->setSingleton(ItemRepository::class,[], [$fileStorage]);
        $container->setSingleton(ItemHydrator::class);
        $container->setSingleton(ItemTypeHydrator::class);
        $container->setSingleton(ItemFormFactory::class, [],[$this->formClasses]);

        if (!$this->has('itemManager'))
        {
            $this->set('itemManager', [
                'class' => ItemManager::class,
                'items' => $this,
            ]);
        }
    }

    /**
     * @param $id
     *
     * @return ItemType
     * @throws InvalidConfigException
     * @throws \svsoft\yii\items\exceptions\ItemTypeNotFoundException
     * @throws \yii\di\NotInstantiableException
     */
    public function getItemTypeById($id)
    {
        return $this->getItemTypeRepository()->get($id);
    }

    /**
     * @param $name
     *
     * @throws InvalidConfigException
     * @throws \svsoft\yii\items\exceptions\ItemTypeNotFoundException
     * @throws \yii\di\NotInstantiableException
     */
    public function getItemTypeByName($name)
    {
        $this->getItemTypeRepository()->getByName($name);
    }

}