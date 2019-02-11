<?php

namespace svsoft\yii\items\widgets;

use yii\web\AssetBundle;

/**
 * Class FileUploadAsset
 * @package svsoft\yii\items\admin\widgets
 */
class FileUploadAsset extends AssetBundle
{
    public $sourcePath = '@svsoft/yii/items/widgets/assets';

    public $css = [
        'css/file-upload-widget.css',
        'test.css'
    ];

    public $js = [
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ];
}