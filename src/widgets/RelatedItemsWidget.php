<?php

namespace svsoft\yii\items\widgets;

use svsoft\yii\items\admin\components\LabelManager;
use svsoft\yii\items\entities\Field;
use svsoft\yii\items\entities\Item;
use svsoft\yii\items\entities\ItemType;
use svsoft\yii\items\repositories\ItemTypeRepository;
use yii\base\InvalidCallException;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Inflector;

class RelatedItemsWidget extends Widget
{
    /**
     * @var ItemType
     */
    public $itemType;

    /**
     * @var Item
     */
    public $item;

    public $listRoute = 'item/index';
    public $detailRoute = 'item/update';

    /**
     * @var LabelManager
     */
    public $labelManager;

    function init()
    {
        /** @var ItemTypeRepository $itemTypeRepository */
        $itemTypeRepository = \Yii::$container->get(ItemTypeRepository::class);

        if (!$this->item)
            throw new InvalidCallException('Property item must be set');

        $this->itemType = $itemTypeRepository->get($this->item->getItemTypeId());

//        if (!$this->itemType)
//            throw new InvalidCallException('Property itemType must be set');


        parent::init();
    }

    function run()
    {
        /** @var ItemTypeRepository $itemTypeRepository */
        $itemTypeRepository = \Yii::$container->get(ItemTypeRepository::class);
        $relatedItemTypes = $itemTypeRepository->getRelatedItemTypes($this->itemType);

        $html = '';
        foreach($relatedItemTypes as $relatedItemType)
        {
            foreach($relatedItemType->getFields() as $field)
            {
                if ($field->getType()->getId() != Field::TYPE_ITEM)
                    continue;

                if ($field->getType()->getParam('itemTypeId') != $this->itemType->getId())
                    continue;

                $html .= $this->renderLink($relatedItemType, $field->getName());
            }
        }

        return $html;
    }

    function renderLink(ItemType $itemType, $fieldName)
    {
        $itemId = $this->item->getId();
        $itemTypeName = $itemType->getName();

        $message =  $this->labelManager ? $this->labelManager->getLabel($itemType)->items : Inflector::pluralize($itemType->getName());

        return Html::a(\Yii::t('items',$message), [$this->listRoute, 'type'=>$itemTypeName, 'relation'=>"{$fieldName}-{$itemId}"],['class'=>'btn btn-default']);
    }

}