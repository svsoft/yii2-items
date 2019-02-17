<?php

namespace svsoft\yii\items;

//use svsoft\yii\items\repositories\FileStorage;
//use svsoft\yii\items\repositories\TableManager;
//use svsoft\yii\items\services\ImageThumb;
//use svsoft\yii\items\services\Items;
//use svsoft\yii\items\services\ItemTypeManager;
use yii\base\BootstrapInterface;

class Bootstrap implements BootstrapInterface
{
    function bootstrap($app)
    {

        return;
////        $container->set('db', function ($container, $params, $config) {
////            return new \yii\db\Connection($config);
////        });
//
//        $container = \Yii::$container;
//
////        $app->db;
////        var_dump('after $app->db');
////
//////        var_dump($container);die();
////
////        $container->setSingleton(Connection::class, function ($container, $params, $config) {
////            return new Connection($config);
////        });
//        $container->setSingleton(Items::class, [
//            'class'=>Items::class,
//            'db' => \Yii::$app->db,
//            'fileStoragePath' => '@app/files',
//
//        ]);
//
//        $container->setSingleton(TableManager::class,[],[\Yii::$app->db]);
//
//        \Yii::$container->setSingleton( FileStorage::class, new FileStorage(\Yii::getAlias('@app/files')));
//        \Yii::$container->setSingleton( ImageThumb::class, [
//            'class'=>ImageThumb::class,
//        ]);
//
//        \Yii::$container->setSingleton(ItemTypeManager::class);
//        \Yii::$container->setSingleton(ItemService::class);
    }
}
