<?php

namespace svsoft\yii\items\repositories\hydrators;

use svsoft\yii\items\entities\ItemType;
use svsoft\yii\items\repositories\TableManager;

class ItemTypeHydrator
{
    /**
     * @var FieldHydrator
     */
    private $fieldHydrator;

    /**
     * @var TableManager
     */
    private $tableManager;

    function __construct(FieldHydrator $fieldHydrator, TableManager $tableManager)
    {
        $this->tableManager = $tableManager;
        $this->fieldHydrator = $fieldHydrator;
    }

    function hydrate($data)
    {
        $fields = [];
        foreach($data['fields'] as $fieldRow)
        {
            $fields[] = $this->fieldHydrator->hydrate($fieldRow);
        }

        $itemType = new ItemType($data['id'], $data['name'], $fields);

        return $itemType;
    }

    function dehytrate(ItemType $itemType)
    {
        $itemTypeKey = $this->tableManager->getTableItemType()->getKey($itemType->getId());

        $fieldRows = [];
        $i = 0;
        foreach($itemType->getFields() as $field)
        {
            $i++;
            $fieldRow = $this->fieldHydrator->dehytrate($field);
            $fieldRow['item_type_key'] = $itemTypeKey;
            $fieldRow['sort'] = $i;
            $fieldRows[] = $fieldRow;
        }

        return [
            'id' => $itemType->getId(),
            'name' => $itemType->getName(),
            'key' => $itemTypeKey,
            'fields' => $fieldRows,
        ];
    }
}