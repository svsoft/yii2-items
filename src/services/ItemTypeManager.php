<?php

namespace svsoft\yii\items\services;

use svsoft\yii\items\entities\Field;
use svsoft\yii\items\entities\ItemType;
use svsoft\yii\items\entities\types\FieldType;
use svsoft\yii\items\exceptions\FieldException;
use svsoft\yii\items\exceptions\FieldNotFoundException;
use svsoft\yii\items\exceptions\ItemTypeException;
use svsoft\yii\items\exceptions\ItemTypeNotFoundException;
use svsoft\yii\items\factories\FieldBuilder;
use svsoft\yii\items\factories\FieldTypeBuilder;
use svsoft\yii\items\factories\FieldTypeFactory;
use svsoft\yii\items\factories\ItemTypeBuilder;
use svsoft\yii\items\repositories\ItemTypeRepository;
use yii\helpers\ArrayHelper;

/**
 * Class ItemService
 * @package svsoft\yii\items\services
 */
class ItemTypeManager
{
    /**
     * @var ItemTypeRepository
     */
    protected $repository;

    /**
     * @var Cacher
     */
    protected $cacher;

    public function __construct(ItemTypeRepository $itemTypeRepository, Cacher $cacher)
    {
        $this->repository = $itemTypeRepository;

        $this->cacher = $cacher;
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

        $this->update($itemType);
    }

    public function removeField(ItemType $itemType, $fieldName)
    {
        $field = $itemType->getFieldByName($fieldName);

        $itemType->removeField($field->getId());

        $this->update($itemType);
    }

    public function renameField(ItemType $itemType, $fieldName, $newFieldName)
    {
        $field = $itemType->getFieldByName($fieldName);

        if ($itemType->hasFieldByName($newFieldName))
            throw new FieldException("Field name \"$newFieldName\" already exist");

        $field->setName($newFieldName);

        $this->update($itemType);
    }

    public function changeType(ItemType $itemType, $fieldName, FieldType $fieldType)
    {
        $field = $itemType->getFieldByName($fieldName);

        $field->setType($fieldType);

        $this->update($itemType);
    }

    public function delete(ItemType $itemType)
    {
        $this->repository->delete($itemType);
        $this->cacher->cleanByItemType($itemType->getId());

    }

    /**
     * @param $data
     *
     * @throws FieldException
     * @throws ItemTypeException
     * @throws ItemTypeNotFoundException
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     * @throws \yii\di\NotInstantiableException
     */
    public function updateFromData($data)
    {
        /** @var FieldTypeFactory $fieldTypeFactory */
        $fieldTypeFactory = \Yii::$container->get(FieldTypeFactory::class);
        foreach($data as $itemTypeData)
        {
            $itemTypeId   = ArrayHelper::getValue($itemTypeData, 'id');
            $itemTypeName = $itemTypeData['name'];

            if (!$itemTypeName)
                throw new ItemTypeException('Item type name must be set');
            try
            {
                if ($itemTypeId)
                    $itemType = $this->repository->get($itemTypeId);
                else
                    $itemType = $this->repository->getByName($itemTypeName);

                $itemTypeId = $itemType->getId();
            }
            catch(ItemTypeNotFoundException $exception)
            {
                $itemTypeId = null;
                $itemType = null;
            }


            $fields = [];
            foreach($itemTypeData['fields'] as $fieldData)
            {
                $fieldId = ArrayHelper::getValue($fieldData, 'id');
                $fieldName = $fieldData['name'];

                if (!$fieldName)
                    throw new FieldException('Field name must be set');

                /** @var FieldTypeBuilder $builder */
                //$builder = \Yii::createObject(FieldTypeBuilder::class);
                if (is_array($fieldData['type']))
                {
                    $typeId = $fieldData['type']['id'];
                    $params = ArrayHelper::getValue($fieldData['type'],'params',[]);

                }
                else
                {
                    $typeId = $fieldData['type'];
                    $params =  [];
                }

                if (isset($params['itemTypeName']))
                {
                    $fieldItemType = $this->repository->getByName($params['itemTypeName']);
                    unset($params['itemTypeName']);
                    $params['itemTypeId'] = $fieldItemType->getId();
                }

                $type = $fieldTypeFactory->build($typeId, $params);

                /** @var FieldBuilder $fieldBuilder */
                $fieldBuilder = \Yii::createObject(FieldBuilder::class);

                if ($itemType)
                {
                    try
                    {
                        if ($fieldId)
                            $field = $itemType->getField($fieldId);
                        else
                            $field = $itemType->getFieldByName($fieldName);

                        $fieldId = $field->getId();
                    }
                    catch(FieldNotFoundException $exception)
                    {
                        $fieldId = null;
                    }

                }

                $field = $fieldBuilder->setId($fieldId)->setName($fieldName)->setType($type)->build();

                $fields[] = $field;
            }

            /** @var ItemTypeBuilder $builder */
            $builder = \Yii::createObject(ItemTypeBuilder::class);


            $itemType = $builder->setId($itemTypeId)->setName($itemTypeName)->setFields($fields)->build();

            if (!$itemTypeId)
                $this->create($itemType);
            else
                $this->update($itemType);
        }
    }

    function update(ItemType $itemType)
    {
        $this->repository->update($itemType);
        $this->cacher->cleanByItemType($itemType->getId());
    }

    function create(ItemType $itemType)
    {
        $this->repository->create($itemType);
    }
}