<?php

namespace svsoft\yii\items\services;

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

class ItemTypeUpdater
{
    /**
     * @var ItemTypeRepository
     */
    protected $repository;

    /**
     * @var FieldTypeFactory
     */
    protected $fieldTypeFactory;

    /**
     * @var ItemTypeManager
     */
    protected $itemTypeManager;

    function __construct(ItemTypeRepository $repository, FieldTypeFactory $fieldTypeFactory, ItemTypeManager $itemTypeManager)
    {
        $this->repository = $repository;
        $this->fieldTypeFactory = $fieldTypeFactory;
        $this->itemTypeManager = $itemTypeManager;
    }

//    function normalize($itemTypesData)
//    {
//        foreach($itemTypesData as $key=>$itemTypeData)
//        {
//            $itemTypeDataNormalize = [];
//            if (is_string($key))
//            {
//                $name = $key;
//            }
//            else
//            {
//                $name = ArrayHelper::getValue($itemTypeData,'name');
//            }
//
//            if (isset($itemTypeData['fields']))
//            {
//
//            }
//
//            $itemTypesData['name'] = $name;
//        }
//
//    }

    function updateItemType($itemTypeData)
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

            $type = $this->fieldTypeFactory->build($typeId, $params);

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
            $this->itemTypeManager->create($itemType);
        else
            $this->itemTypeManager->update($itemType);
    }
}
