<?php

namespace svsoft\yii\items\repositories\hydrators;

class ValueIntHydrator implements ValueHydratorInterface
{
    function hydrate($valueRow)
    {
        return (int)$valueRow['value_int'];
    }

    function dehytrate($value)
    {
        return $value;
    }
}