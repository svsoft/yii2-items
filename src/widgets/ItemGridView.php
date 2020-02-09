<?php

namespace svsoft\yii\items\widgets;

use svsoft\thumbnails\handlers\ResizeHandler;
use svsoft\thumbnails\Thumb;
use svsoft\thumbnails\ThumbnailsInterface;
use svsoft\yii\items\admin\components\Label;
use svsoft\yii\items\entities\Field;
use svsoft\yii\items\entities\Item;
use svsoft\yii\items\entities\ItemType;
use svsoft\yii\items\repositories\ItemRepository;
use svsoft\yii\items\traits\GetThumbnailsTrait;
use yii\base\InvalidCallException;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;

class ItemGridView extends GridView
{

    use GetThumbnailsTrait;

    /**
     * @var ItemType
     */
    public $itemType;

    /**
     * @var Label
     */
    public $itemTypeLabels;

    /**
     * @var
     */
    public $labels = [];

    /**
     * Список скрытых колонок
     *
     * @var array
     */
    public $hiddenColumns = [];

    /**
     * Список видемых колонок, если пустой массив, то выводятся все колонки соответсвубщие ItemType
     *
     * @var array
     */
    public $visibleColumns = [];


    /**
     * Возвращает список колонок для вывода
     *
     * @return array
     */
    protected function getColumnList()
    {
        $columns[] = 'id';
        foreach($this->itemType->getFields() as $field)
        {
            $columns[] = $field->getName();
        }

        foreach($columns as $key=>$columnName)
        {
            if ($this->visibleColumns && !in_array($columnName, $this->visibleColumns))
                unset($columns[$key]);

            if (in_array($columnName, $this->hiddenColumns))
                unset($columns[$key]);
        }

        return $columns;
    }

    /**
     * @throws \svsoft\yii\items\exceptions\FieldNotFoundException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    function init()
    {
        if (!$this->itemType)
            throw new InvalidCallException('Property itemType must be set');

        /** @var ItemRepository $itemRepository */
        $itemRepository = \Yii::$container->get(ItemRepository::class);

        $columnList = $this->getColumnList();

        /** @var ThumbnailsInterface $thumbnails */
        $thumb = new Thumb([new ResizeHandler(60,60)]);
        $thumbnails = $this::getThumbnails();

        foreach($columnList as $columnName)
        {
            $additionalColumn = [
                'attribute' => $columnName,
                'label' => ArrayHelper::getValue($this->labels, $columnName, Inflector::camel2words($columnName, true)),
            ];

            if ($columnName != 'id')
            {
                $field = $this->itemType->getFieldByName($columnName);
                $fieldName = $field->getName();

                if (isset($this->labels[$fieldName]))
                    $additionalColumn['label'] = $this->labels[$fieldName];

                if ($field->getType()->getId() == Field::TYPE_FILE)
                {

                    $additionalColumn['format'] = 'raw';
                    $additionalColumn['value'] = function (Item $item) use ($field, $itemRepository, $thumbnails, $thumb) {

                        if (!$attributeValue = $item->getAttribute($field->getName()))
                            return null;

                        $filePaths = [];
                        if ($field->getMultiple())
                        {
                            foreach($attributeValue as $fileAttribute)
                            {
                                $filePaths[] = $fileAttribute->getFilePath();
                            }
                        }
                        else
                        {
                            $filePaths = [$attributeValue->getFilePath()];
                        }


                        $values = array_map(function ($value) use ($thumbnails, $thumb) {

                            if (!file_exists($value))
                                return pathinfo($value, PATHINFO_BASENAME);

                            if (getimagesize($value) === false)
                                return pathinfo($value, PATHINFO_BASENAME);

                            return $value ? Html::img($thumbnails->getCreator()->create($value, $thumb)) : '';
                        }, $filePaths);

                        return implode(',', $values);
                    };
                }
                elseif ($field->getType()->getId() == Field::TYPE_ITEM)
                {
                    $additionalColumn['value'] = function(Item $item) use ($field, $itemRepository){

                        if (!$attributeValue = $item->getAttribute($field->getName()))
                            return null;

                        $values = [];
                        if ($field->getMultiple())
                        {
                            foreach($attributeValue as $value)
                            {
                                $values[] = (string)$itemRepository->get($value);
                            }
                        }
                        else
                        {
                            $values = [(string)$itemRepository->get($attributeValue)];
                        }

                        return implode(',', $values);
                    };
                }
                elseif ($field->getMultiple())
                {

                    $additionalColumn['value'] = function(Item $item) use ($field){

                        return implode(',', $item->getAttribute($field->getName()));
                    };
                }
            }

            $additionalColumns[$columnName] = $additionalColumn;
        }

        $this->columns = ArrayHelper::merge($additionalColumns, $this->columns);

        parent::init();
    }

}
