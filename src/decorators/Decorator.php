<?php

namespace svsoft\yii\items\decorators;

use svsoft\yii\items\entities\Field;
use svsoft\yii\items\entities\FileAttributeInterface;
use svsoft\yii\items\entities\Item;
use svsoft\yii\items\exceptions\FieldNotFoundException;
use svsoft\yii\items\exceptions\ItemAttributeNotFound;
use svsoft\yii\items\exceptions\ItemTypeNotFoundException;
use svsoft\yii\items\repositories\ItemTypeRepository;
use svsoft\yii\items\services\Cacher;
use svsoft\yii\items\services\ImageThumb;
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

    /** @var ImageThumb::class */
    public $imageThumb;

    function __construct(ItemTypeRepository $itemTypeRepository, Cacher $cacher, ImageThumb $imageThumb)
    {
        $this->itemTypeRepository = $itemTypeRepository;

        $this->cacher = $cacher;

        $this->imageThumb = $imageThumb;

        parent::__construct([]);
    }

    /**
     * @param Item $item
     * @param $attribute
     *
     * @return mixed|string
     */
    function get(Item $item, $attribute)
    {
        $cacheKey =  [$item->getId(), $attribute];

        if (($decoratedValue = $this->cacher->get($cacheKey)) === false)
        {
            try
            {
                $itemType = $this->itemTypeRepository->get($item->getItemTypeId());
                $field = $itemType->getFieldByName($attribute);
            }
            catch(ItemTypeNotFoundException $exception)
            {
                \Yii::error($exception);
                return '';
            }
            catch(FieldNotFoundException $exception)
            {
                \Yii::error($exception);
                return '';
            }

            $typeId = $field->getType()->getId();

            try
            {
                $attributeValue = $item->getAttribute($attribute);
            }
            catch(ItemAttributeNotFound $exception)
            {
                \Yii::error($exception);
                return '';
            }


            switch($typeId)
            {
                case Field::TYPE_TEXT:
                    $decoratedValue = nl2br(Html::encode($attributeValue));
                    break;
                case Field::TYPE_HTML:
                    $decoratedValue = HtmlPurifier::process($attributeValue);
                    break;
                default:
                    $decoratedValue = Html::encode($attributeValue);
            }

            $this->cacher->set($cacheKey, $decoratedValue, $itemType->getId());
        }

        return $decoratedValue;
    }

    function thumb(Item $item, $attribute, $thumbName)
    {
        $attributeValue = $item->getAttribute($attribute);

        if (is_array($attributeValue))
        {
            $return = [];
            foreach($attributeValue as $value)
            {
                if ($value instanceof FileAttributeInterface)
                    $return[] = $this->imageThumb->thumb($value->getFilePath(), $thumbName);
            }
        }
        else
        {
            $return = null;
            $value = $attributeValue;
            if ($value instanceof FileAttributeInterface)
                $return = $this->imageThumb->thumb($value->getFilePath(), $thumbName);
        }

        return $return;
    }
}
