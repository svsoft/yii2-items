<?php

namespace svsoft\yii\items\traits;

use svsoft\yii\items\repositories\ItemTypeRepository;
use yii\base\InvalidConfigException;

trait GetItemTypeRepositoryTrait
{
    /**
     * @return ItemTypeRepository
     */
    protected static function getItemTypeRepository()
    {
        try
        {
            /** @var ItemTypeRepository $repo */
            $repo = \Yii::$container->get(ItemTypeRepository::class);
        }
        catch(InvalidConfigException $exception) {}

        return $repo;
    }
}