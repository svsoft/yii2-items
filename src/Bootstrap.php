<?php

namespace svsoft\yii\items;

use svsoft\yii\items\repositories\FileStorage;
use svsoft\yii\items\repositories\TableManager;
use svsoft\yii\items\services\ImageThumb;
use yii\base\BootstrapInterface;
use yii\db\Connection;

class Bootstrap implements BootstrapInterface
{
    function bootstrap($app)
    {
        \Yii::$container->setSingleton( Connection::class, \Yii::$app->db );
        \Yii::$container->setSingleton( TableManager::class );
        \Yii::$container->setSingleton( FileStorage::class, new FileStorage(\Yii::getAlias('@app/files')));
        \Yii::$container->setSingleton( ImageThumb::class, [
            'class'=>ImageThumb::class,
        ]);
    }
}