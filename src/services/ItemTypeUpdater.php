<?php

namespace svsoft\yii\items\services;

use svsoft\yii\items\exceptions\FieldException;
use svsoft\yii\items\exceptions\FieldNotFoundException;
use svsoft\yii\items\exceptions\ItemTypeException;
use svsoft\yii\items\exceptions\ItemTypeNotFoundException;
use svsoft\yii\items\factories\FieldBuilder;
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

    protected function normalize($itemTypesData)
    {
        $itemTypesDataNormalize = [];
        foreach($itemTypesData as $key=>$itemTypeData)
        {
            $name = is_string($key) ? $key : '';

            $itemTypesDataNormalize[] = $this->normalizeItemType($itemTypeData, $name);
        }

        return $itemTypesDataNormalize;
    }

    protected function normalizeItemType($data, $name = null)
    {
        if (isset($data['fields']))
        {
            $fields = $data['fields'];
            $dataNormalize = $data;
            unset($dataNormalize['fields']);
        }
        else
        {
            $fields = $data;
            $dataNormalize =[];
        }
        if ($name)
            $dataNormalize['name'] = $name;

        foreach($fields as $fieldKey=>$fieldData)
        {
            $fieldName = is_numeric($fieldKey) ? null : $fieldKey;
            $dataNormalize['fields'][] = $this->normalizeField($fieldData, $fieldName);
        }

        return $dataNormalize;

    }

    protected function normalizeField($data, $name = null)
    {
        if (is_array($data))
        {
            $normalizeData = $data;
            unset($normalizeData['type']);
            $normalizeData['type'] = $this->normalizeType($data['type']);

        }
        else
        {
            $normalizeData['type'] = $this->normalizeType($data);
        }

        if ($name)
            $normalizeData['name'] = $name;

        return $normalizeData;
    }

    protected function normalizeType($data)
    {
        if (is_array($data))
        {
            $normalizeData = $data;
        }
        else
        {
            $normalizeData = [
                'id' => $data,
            ];
        }

        return $normalizeData;
    }

    public function update($itemTypesData)
    {
        $itemTypesData = $this->normalize($itemTypesData);

        foreach($itemTypesData as $itemTypeData)
        {
            $this->updateItemType($itemTypeData);
        }
    }

    function updateItemType($itemTypeData, $normalize = false)
    {
        if ($normalize)
            $itemTypeData = $this->normalizeItemType($itemTypeData);

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

            $typeData = $fieldData['type'];
            $typeId = $typeData['id'];
            $params = $typeData;
            unset($params['id']);

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
