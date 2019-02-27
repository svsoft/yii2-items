<?php

namespace svsoft\yii\items\repositories\hydrators;

class ValueDateHydrator implements ValueHydratorInterface
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