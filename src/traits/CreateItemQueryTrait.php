<?php

namespace svsoft\yii\items\traits;

use svsoft\yii\items\repositories\ItemQuery;
use yii\base\InvalidConfigException;

trait CreateItemQueryTrait
{
    /**
     * @param $itemType
     *
     * @return object|\svsoft\yii\items\repositories\ItemQuery
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    protected function createQuery($itemType)
    {
        return \Yii::$container->get(ItemQuery::class,[$itemType]);
    }

    /**
     * @param $itemType
     *
     * @return object|ItemQuery|null
     */
    protected static function createItemQuery($itemType)
    {
        try
        {
            return \Yii::$container->get(ItemQuery::class,[$itemType]);
        }
        catch(InvalidConfigException $exception){};
    }
}