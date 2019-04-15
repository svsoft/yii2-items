<?php

namespace svsoft\yii\items\traits;

use svsoft\yii\items\repositories\ItemTypeRepository;

trait GetItemTypeRepositoryTrait
{
    /**
     * @return ItemTypeRepository
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    protected static function getItemTypeRepository()
    {
        /** @var ItemTypeRepository $repo */
        $repo = \Yii::$container->get(ItemTypeRepository::class);

        return $repo;
    }
}