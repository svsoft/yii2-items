<?php

namespace svsoft\yii\items\repositories\hydrators;

class ValueBooleanHydrator implements ValueHydratorInterface
{
    function hydrate($value)
    {
        return (bool)$value;
    }

    function dehytrate($value)
    {
        return !$value ? null : true;
    }
}