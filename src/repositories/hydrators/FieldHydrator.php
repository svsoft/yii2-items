<?php

namespace svsoft\yii\items\repositories\hydrators;

use svsoft\yii\items\entities\Field;
use svsoft\yii\items\repositories\TableManager;

class FieldHydrator
{

    /**
     * @var TableManager
     */
    private $tableManager;

    function __construct(TableManager $tableManager)
    {
        $this->tableManager = $tableManager;
    }

    function hydrate($data)
    {
        $field = new Field($data['id'], $data['name'], $data['type'], (bool)$data['multiple']);
        return $field;
    }

    function dehytrate(Field $field)
    {
        return [
            'id'       => $field->getId(),
            'name'     => $field->getName(),
            'type'     => $field->getType(),
            'multiple' => (int)$field->getMultiple(),
            'key' => $this->tableManager->getTableField()->getKey($field->getId()),
        ];
    }
}