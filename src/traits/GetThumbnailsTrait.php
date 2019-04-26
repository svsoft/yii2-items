<?php

namespace svsoft\yii\items\traits;

use svsoft\thumbnails\ThumbnailsInterface;
use yii\base\InvalidConfigException;

/**
 * Trait GetThumbnailsTrait
 * @package svsoft\yii\items\traits
 *
 * @author Shiryakov Viktor <shiryakovv@gmail.com>
 */
trait GetThumbnailsTrait
{

    /**
     *
     * @return ThumbnailsInterface
     */
    protected static function getThumbnails()
    {
        try
        {
            /** @var ThumbnailsInterface $component */
            $component = \Yii::$container->get('items-thumbnails');
        }
        catch(InvalidConfigException $exception){};

        return $component;
    }
}