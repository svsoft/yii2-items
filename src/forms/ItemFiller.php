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
use svsoft\yii\items\entities\UploadedFileAttribute;
use yii\web\UploadedFile;

class ItemFiller
{

    private function hydrateFile($value)
    {
        if ($value instanceof UploadedFile)
            return new UploadedFileAttribute($value);

        if ($value && is_string($value))
            return new FileAttribute($value);

        return null;
    }

    /**
     * @param ItemForm $itemForm
     * @param Item $item
     *
     * @throws \svsoft\yii\items\exceptions\ItemAttributeNotFound
     */
    function fill(Item $item, ItemForm $itemForm)
    {
        foreach($itemForm->itemType->getFields() as $field)
        {
            $name = $field->getName();
            if ($field->getType()->getId() === Field::TYPE_FILE)
            {
                if ($field->getMultiple())
                {
                    $attributeValue = [];
                    foreach($itemForm->getAttribute($name) as $file)
                        $attributeValue[] = $this->hydrateFile($file);
                }
                else
                {
                    $attributeValue = $this->hydrateFile($itemForm->getAttribute($name));
                }

                $item->setAttribute($name, $attributeValue);
            }
            else
            {
                $item->setAttribute($name, $itemForm->getAttribute($name));
            }
        }
    }

}