<?php

namespace svsoft\yii\items\repositories\hydrators;

class ValueIntHydrator implements ValueHydratorInterface
{
    function hydrate($value)
    {
        return (int)$value;
    }

    function dehytrate($value)
    {
        return $value;
    }
}