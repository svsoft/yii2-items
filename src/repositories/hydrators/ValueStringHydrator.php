<?php

namespace svsoft\yii\items\repositories\hydrators;

class ValueStringHydrator implements ValueHydratorInterface
{
    function hydrate($valueRow)
    {
        return $valueRow['value_string'];
    }

    function dehytrate($value)
    {
        return $value;
    }
}