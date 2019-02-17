<?php

namespace svsoft\yii\items\repositories\hydrators;

class ValueRealHydrator implements ValueHydratorInterface
{
    function hydrate($value)
    {
        return $value !== null ? (float)$value : null;
    }

    function dehytrate($value)
    {
        return $value;
    }
}