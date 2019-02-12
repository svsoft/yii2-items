<?php

namespace svsoft\yii\items\repositories\hydrators;

class ValueRealHydrator implements ValueHydratorInterface
{
    function hydrate($value)
    {
        return (float)$value;
    }

    function dehytrate($value)
    {
        return $value;
    }
}