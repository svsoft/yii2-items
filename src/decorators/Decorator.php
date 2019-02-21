<?php

namespace svsoft\yii\items\decorators;

use svsoft\yii\items\entities\Field;
use svsoft\yii\items\entities\Item;
use svsoft\yii\items\repositories\ItemTypeRepository;
use svsoft\yii\items\services\Cacher;
use yii\base\BaseObject;
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;

class Decorator extends BaseObject
{
    /**
     * @var ItemTypeRepository
     */
    protected $itemTypeRepository;

    /**
     * @var Cacher
     */
    protected $cacher;

    function init()
    {
        $this->itemTypeRepository = \Yii::$container->get(ItemTypeRepository::class);

        $this->cacher = \Yii::$container->get(Cacher::class);

        parent::init();
    }

    function get(Item $item, $attribute)
    {
        $cacheKey =  [$item->getId(), $attribute];

        if (($decoratedValue = $this->cacher->get($cacheKey)) === false)
        {
            $itemType = $this->itemTypeRepository->get($item->getItemTypeId());

            $field = $itemType->getFieldByName($attribute);

            $typeId = $field->getType()->getId();
            switch($typeId)
            {
                case Field::TYPE_TEXT:
                    $decoratedValue = nl2br($item->getAttribute($attribute));
                    break;
                case Field::TYPE_HTML:
                    $decoratedValue = HtmlPurifier::process($item->getAttribute($attribute));
                    break;
                default:
                    $decoratedValue = Html::encode($item->getAttribute($attribute));
            }

            $this->cacher->set($cacheKey, $decoratedValue, $itemType->getId());
        }

        return $decoratedValue;
    }


}
