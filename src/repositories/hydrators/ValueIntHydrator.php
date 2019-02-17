<?php

namespace svsoft\yii\items\repositories\hydrators;

class ValueIntHydrator implements ValueHydratorInterface
{
    function hydrate($value)
    {
        return $value !== null ? (int)$value : null;
    }

    function dehytrate($value)
    {
        return $value;
    }
}