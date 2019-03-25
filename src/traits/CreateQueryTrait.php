<?php

namespace svsoft\yii\items\traits;

use svsoft\yii\items\repositories\ItemQuery;

trait CreateQueryTrait
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
}