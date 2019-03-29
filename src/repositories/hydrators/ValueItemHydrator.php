<?php

namespace svsoft\yii\items\repositories\hydrators;

use svsoft\yii\items\repositories\TableManager;

class ValueItemHydrator implements ValueHydratorInterface
{
    /**
     * @var TableManager
     */
    protected $tableManager;

    function __construct(TableManager $tableManager)
    {
        $this->tableManager = $tableManager;
    }

    function hydrate($value)
    {
        return $value;
        //return $this->tableManager->getTableItem()->getId($value);
    }

    function dehytrate($value)
    {
        return $value ?: null ;
        //return $this->tableManager->getTableItem()->getKey($value);
    }
}