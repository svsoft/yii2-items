<?php
/**
 * Created by PhpStorm.
 * User: viktor
 * Date: 08.02.2019
 * Time: 11:37
 */

namespace svsoft\yii\items\forms;

use svsoft\yii\items\entities\Field;
use svsoft\yii\items\entities\FileAttribute;
use svsoft\yii\items\entities\Item;

class ItemFormFiller
{

    /**
     * @param Item $item
     * @param ItemForm $itemForm
     *
     * @throws \svsoft\yii\items\exceptions\ItemAttributeNotFound
     */
    function fill(Item $item, ItemForm $itemForm)
    {
        $formAttributes = [];
        foreach($itemForm->itemType->getFields() as $field)
        {
            $name = $field->getName();
            if ($field->getType() === Field::TYPE_FILE)
            {
                $value = $item->getAttribute($name);

                if (is_array($value))
                {
                    $attributeValue = [];
                    $values = $value;
                    /** @var FileAttribute $value */
                    foreach($values as $value)
                    {
                        $attributeValue[] = $value->getFileName();
                    }
                }
                else
                {
                    $attributeValue = $value ? $value->getFileName() : null;
                }

                $formAttributes[$name] = $attributeValue;
            }
            else
            {
                $formAttributes[$name] = $item->getAttribute($name);
            }
        }

        $itemForm->setAttributes($formAttributes, false);
    }

}