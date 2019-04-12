<?php

namespace svsoft\yii\items;

use svsoft\yii\imagethumb\ThumbManager;
use svsoft\yii\imagethumb\ThumbManagerInterface;
use svsoft\yii\items\decorators\Decorator;
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
use svsoft\yii\items\services\ImageThumb;
use yii\base\BootstrapInterface;
use yii\base\InvalidConfigException;
use yii\console\Application;
use yii\helpers\ArrayHelper;
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
     * @var ThumbManager
     */
    public $thumbManager = 'thumbManager';

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

        $this->initThumbManager($app);

        $fileStorage = $container->get(FileStorage::class, [$this->fileStoragePath]);
        $container->setSingleton(TableManager::class, [], [\Yii::$app->db]);
        $container->setSingleton(ItemTypeRepository::class, [], [$fileStorage]);
        $container->setSingleton(ItemRepository::class, [], [$fileStorage]);
        $container->setSingleton(ItemHydrator::class, [], [$fileStorage]);
        $container->setSingleton(ItemTypeHydrator::class);
        $container->setSingleton(ItemFormFactory::class, [], [$this->formClasses]);
        $container->setSingleton(SaveModelFactory::class, [], [$this->saveModelClasses]);
        $container->setSingleton(ItemFactory::class, [], [$this->itemClasses]);
        $container->setSingleton(Decorator::class);
        $container->setSingleton(Cacher::class);

        if ($app instanceof Application) {
            if (empty($app->controllerMap['migrate']['class'])) {

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

        return;
    }

    /**
     * @param \yii\base\Application $app
     *
     */
    private function initThumbManager($app)
    {
        $container = \Yii::$container;

        /** @var $thumbManager ThumbManagerInterface|array */
        if (is_string($this->thumbManager))
            $thumbManager = ArrayHelper::getValue($app->getComponents(), $this->thumbManager);
        elseif (is_array($this->thumbManager))
            $thumbManager = $this->thumbManager;
        elseif (is_callable($this->thumbManager))
            $thumbManager = $this->thumbManager;
        elseif ($this->thumbManager instanceof ThumbManagerInterface)
            $thumbManager = $this->thumbManager;

        if ($thumbManager)
        {
            $container->setSingleton(ThumbManagerInterface::class, $thumbManager);
        }
        elseif ($container->has(ImageThumb::class))
        {
            $container->setSingleton(ThumbManagerInterface::class, $container->getDefinitions()[ImageThumb::class]);
        }
    }
}
