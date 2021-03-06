<?php

namespace svsoft\yii\items;

use svsoft\yii\items\factories\ItemFactory;
use svsoft\yii\items\factories\ItemFormFactory;
use svsoft\yii\items\factories\SaveModelFactory;
use svsoft\yii\items\repositories\FileStorage;
use svsoft\yii\items\repositories\hydrators\ItemHydrator;
use svsoft\yii\items\repositories\hydrators\ItemTypeHydrator;
use svsoft\yii\items\repositories\ItemRepository;
use svsoft\yii\items\repositories\ItemTypeRepository;
use svsoft\yii\items\repositories\TableManager;
use svsoft\yii\items\services\Cacher;
use yii\base\BootstrapInterface;
use yii\base\InvalidConfigException;
use yii\console\Application;
use yii\di\Instance;
use yii\i18n\PhpMessageSource;

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

    public $saveModelClasses = [];

    /**
     * Соответсвие типов элементов и классов форм
     *
     * @var array
     */
    public $itemClasses = [];


    /**
     * Ид компонента приложения реализующего интерфейс svsoft\thumbnails\ThumbnailsInterface
     * @var string
     */
    public $thumbnails = 'thumbnails';

    /**
     * Ид компонента приложения \yii\db\Connection
     * @var string
     */
    public $db = 'db';

    /**
     * @param \yii\base\Application $app
     *
     * @throws InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    function bootstrap($app)
    {
        if (!$this->fileStoragePath) {
            throw new InvalidConfigException('Property fileStoragePath must be set');
        }

        $this->fileStoragePath = \Yii::getAlias($this->fileStoragePath);

        $container = \Yii::$container;

        $container->setSingleton('items-file-storage', function () use ($app) {
            return \Yii::$container->get(FileStorage::class, [$this->fileStoragePath]);
        });

        $container->setSingleton('items-db', function () use ($app) {
            return \Yii::$app->get($this->db);
        });

        $container->setSingleton('items-thumbnails', function () use ($app) {
            return \Yii::$app->get($this->thumbnails);
        });

        $container->setSingleton(TableManager::class, [], [Instance::of('items-db')]);
        $container->setSingleton(ItemTypeRepository::class, [], [Instance::of('items-file-storage')]);
        $container->setSingleton(ItemRepository::class, [], [Instance::of('items-file-storage')]);
        $container->setSingleton(ItemHydrator::class, [], [Instance::of('items-file-storage')]);
        $container->setSingleton(ItemTypeHydrator::class);
        $container->setSingleton(ItemFormFactory::class, [], [$this->formClasses]);
        $container->setSingleton(SaveModelFactory::class, [], [$this->saveModelClasses]);
        $container->setSingleton(ItemFactory::class, [], [$this->itemClasses]);
        $container->setSingleton(Cacher::class);

        if ($app instanceof Application)
        {
            if (empty($app->controllerMap['migrate']['class']))
            {

                $app->controllerMap['migrate'] = [
                    'class' => 'yii\console\controllers\MigrateController',
                ];
            }

            $app->controllerMap['migrate']['migrationNamespaces'][] = 'svsoft\yii\items\migrations';
        }

        $i18n = $app->getI18n();

        if (!isset($i18n->translations['items']) && !isset($i18n->translations['items*'])) {

            $i18n->translations['items*'] = [
                'class' => PhpMessageSource::class,
                'basePath' => '@svsoft/yii/items/messages',
            ];
        }
    }
}
