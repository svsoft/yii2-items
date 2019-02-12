<?php

namespace svsoft\yii\items\services;

use svsoft\yii\items\entities\Field;
use svsoft\yii\items\entities\ItemType;
use svsoft\yii\items\exceptions\FieldException;
use svsoft\yii\items\repositories\ItemTypeRepository;
use yii\base\BaseObject;

/**
 * Class ItemService
 * @package svsoft\yii\items\services
 */
class ItemTypeService extends BaseObject
{
    /**
     * @var ItemTypeRepository
     */
    protected $repository;

    public function __construct(ItemTypeRepository $itemTypeRepository)
    {
        $this->repository = $itemTypeRepository;
        parent::__construct([]);
    }

    /**
     * @param ItemType $itemType
     * @param Field $newField
     *
     * @throws FieldException
     * @throws \yii\db\Exception
     */
    public function addField(ItemType $itemType, Field $newField)
    {
        $itemType->addField($newField);

        $this->repository->update($itemType);
    }

    public function removeField(ItemType $itemType, $fieldName)
    {
        $field = $itemType->getFieldByName($fieldName);

        $itemType->removeField($field->getId());

        $this->repository->update($itemType);
    }

    public function renameField(ItemType $itemType, $fieldName, $newFieldName)
    {
        $field = $itemType->getFieldByName($fieldName);

        if ($itemType->hasFieldByName($newFieldName))
            throw new FieldException("Field name \"$newFieldName\" already exist");

        $field->setName($newFieldName);

        $this->repository->update($itemType);
    }

    public function changeType(ItemType $itemType, $fieldName, $newType, $newMultiple = null)
    {
        $field = $itemType->getFieldByName($fieldName);

        $field->setType($newType);

        if ($newMultiple !== null)
            $field->setMultiple($newMultiple);

        $this->repository->update($itemType);
    }
}