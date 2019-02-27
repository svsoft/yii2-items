<?php

namespace svsoft\yii\items\repositories\hydrators;

class ValueDatetimeHydrator implements ValueHydratorInterface
{
    function hydrate($value)
    {
        return $value;
    }

    function dehytrate($value)
    {
        return $value;
    }
}