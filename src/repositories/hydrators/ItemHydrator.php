<?php

namespace svsoft\yii\items\repositories\hydrators;

use svsoft\yii\items\entities\Field;
use svsoft\yii\items\entities\Item;
use svsoft\yii\items\factories\ItemFactory;
use svsoft\yii\items\repositories\FileStorage;
use svsoft\yii\items\repositories\ItemTypeRepository;
use svsoft\yii\items\repositories\TableManager;

class ItemHydrator
{
    /**
     * @var ValueHydratorInterface[]
     */
    private $valueHydrators = [];

    /**
     * @var ItemTypeRepository
     */
    private $itemTypeRepository;


    /**
     * @var TableManager
     */
    private $tableManager;

    /**
     * @var ItemFactory
     */
    private $itemFactory;


    function __construct(FileStorage $fileStorage, ItemTypeRepository $itemTypeRepository, TableManager $tableManager, ItemFactory $itemFactory)
    {
        $this->tableManager = $tableManager;
        $this->itemTypeRepository = $itemTypeRepository;
        $this->itemFactory = $itemFactory;

        $this->valueHydrators = [
            Field::TYPE_STRING => new ValueStringHydrator(),
            Field::TYPE_INT    => new ValueIntHydrator(),
            Field::TYPE_REAL   => new ValueRealHydrator(),
            Field::TYPE_TEXT   => new ValueStringHydrator(),
            Field::TYPE_FILE   => new ValueFileHydrator($fileStorage),
            Field::TYPE_HTML   => new ValueStringHydrator(),
            Field::TYPE_ITEM   => new ValueItemHydrator($tableManager),
        ];
    }

    function hydrate($itemRow)
    {
        $itemTypeKey = $itemRow['item_type_key'];
        $itemTypeId = $this->tableManager->getTableItemType()->getId($itemTypeKey);
        $itemType = $this->itemTypeRepository->get($itemTypeId);

        $valueRowsGroupByField = [];

        foreach($itemRow['values'] as $valueRow)
        {
            $valueRowsGroupByField[$valueRow['field_key']][] = $valueRow;
        }

        $attributes = [];
        foreach($itemType->getFields() as $field)
        {
            $attributes[$field->getName()] = $field->getMultiple() ? [] : null;
        }

        foreach($valueRowsGroupByField as $fieldKey=>$valueRows)
        {
            $fieldId = $this->tableManager->getTableField()->getId($fieldKey);
            $field = $itemType->getField($fieldId);

            $valueColumn = $this->tableManager->getTableValue()->getValueColumn($field->getType()->getId());

            $hydrateValues = [];
            foreach($valueRows as $valueRow)
                $hydrateValues[] = $this->getValueHydrator($field)->hydrate($valueRow[$valueColumn]);

            $attributes[$field->getName()] = $field->getMultiple() ? $hydrateValues : current($hydrateValues);
        }

        $item = $this->itemFactory->build($itemRow['id'], $itemType, $attributes);

        return $item;
    }

    function dehytrate(Item $item)
    {
        // $itemKey = $this->keyConnector->getItemKey($item->getId());
        $itemKey = $this->tableManager->getTableItem()->getKey($item->getId());
        $itemType = $this->itemTypeRepository->get($item->getItemTypeId());

        $itemData = [
            'id'=>$item->getId(),
            'item_type_key'=>$this->tableManager->getTableItemType()->getKey($itemType->getId()),
        ];
        if ($itemKey)
            $itemData['key'] = $itemKey;


        $attributes = $item->getAttributes();

        $valueRow = [
            'item_key'=>$itemKey,
        ];

        foreach($itemType->getFields() as $field)
        {
            $fieldKey = $this->tableManager->getTableField()->getKey($field->getId());
            $valueRow['field_key'] = $fieldKey;

            $value = $attributes[$field->getName()];

            if (is_array($value))
                $values = $value;
            else
                $values = [$value];

            foreach($values as $value)
            {
                $valueColumn = $this->tableManager->getTableValue()->getValueColumn($field->getType()->getId());
                $value = $this->getValueHydrator($field)->dehytrate($value);

                if ($value !== null)
                    $itemData['values'][] = array_merge($valueRow, [$valueColumn => $value]);
            }
       }

        return $itemData;
    }

    /**
     * @param $type
     *
     * @return ValueHydratorInterface
     */

    /**
     * @param Field $field
     *
     * @return ValueHydratorInterface
     */
    function getValueHydrator(Field $field)
    {
        return $this->valueHydrators[$field->getType()->getId()];
    }
}