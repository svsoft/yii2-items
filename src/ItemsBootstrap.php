<?php

namespace svsoft\yii\items;

use svsoft\yii\items\factories\ItemFactory;
use svsoft\yii\items\factories\ItemFormFactory;
use svsoft\yii\items\repositories\FileStorage;
use svsoft\yii\items\repositories\hydrators\ItemHydrator;
use svsoft\yii\items\repositories\hydrators\ItemTypeHydrator;
use svsoft\yii\items\repositories\ItemRepository;
use svsoft\yii\items\repositories\ItemTypeRepository;
use svsoft\yii\items\repositories\TableManager;
use yii\base\BootstrapInterface;
use yii\base\InvalidConfigException;

class ItemsBootstrap implements BootstrapInterface
{
    /**
     * @var string
     */
    public $fileStoragePath;

    /**
     * Соответсвие типов элементов и классов форм
     * @var array
     */
    public $formClasses = [];

    /**
     * Соответсвие типов элементов и классов форм
     *
     * @var array
     */
    public $itemClasses = [];


    function bootstrap($app)
    {
        if (!$this->fileStoragePath)
            throw new InvalidConfigException('Property fileStoragePath must be set');

        $this->fileStoragePath = \Yii::getAlias($this->fileStoragePath);

        $container = \Yii::$container;

        $fileStorage = $container->get(FileStorage::class,[$this->fileStoragePath]);

        $container->setSingleton(TableManager::class, [], [\Yii::$app->db]);
        $container->setSingleton(ItemTypeRepository::class, [], [$fileStorage]);
        $container->setSingleton(ItemRepository::class,[], [$fileStorage]);
        $container->setSingleton(ItemHydrator::class, [], [$fileStorage]);
        $container->setSingleton(ItemTypeHydrator::class);
        $container->setSingleton(ItemFormFactory::class, [],[$this->formClasses]);
        $container->setSingleton(ItemFactory::class, [],[ $this->itemClasses]);

        return;
    }
}
