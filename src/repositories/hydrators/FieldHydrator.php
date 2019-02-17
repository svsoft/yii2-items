<?php

namespace svsoft\yii\items\repositories\hydrators;

use svsoft\yii\items\entities\Field;
use svsoft\yii\items\factories\FieldTypeFactory;
use svsoft\yii\items\repositories\TableManager;

class FieldHydrator
{

    /**
     * @var TableManager
     */
    private $tableManager;

    /**
     * @var FieldTypeFactory
     */
    private $fieldTypeFactory;

    function __construct(TableManager $tableManager, FieldTypeFactory $fieldTypeFactory)
    {
        $this->tableManager = $tableManager;
        $this->fieldTypeFactory = $fieldTypeFactory;
    }

    function hydrate($data)
    {
        if (!$data['params'])
            $params = [];
        else
            $params = unserialize($data['params']);

        if ($key = $data['field_type_item_type_key'])
            $params['item_type_id'] = $this->tableManager->getTableItemType()->getId($key);

        $type = $this->fieldTypeFactory->build($data['type'], $params);

        $field = new Field($data['id'], $data['name'], $type);

        return $field;
    }

    function dehytrate(Field $field)
    {
        $fieldTypeItemTypeKey = null;
        if ($itemTypeId = $field->getType()->getParam('itemTypeId'))
        {
            $fieldTypeItemTypeKey = $this->tableManager->getTableItemType()->getKey($itemTypeId);
        }

        $data = [
            'id'       => $field->getId(),
            'name'     => $field->getName(),
            'type'     => $field->getType()->getId(),
            //'multiple' => (int)$field->getType()->getMultiple(),
            'key' => $this->tableManager->getTableField()->getKey($field->getId()),
            'field_type_item_type_key' => $fieldTypeItemTypeKey,
            'params'   => serialize($field->getType()->getParams())
        ];

        return $data;
    }
}