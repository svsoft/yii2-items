<?php

namespace svsoft\yii\items\repositories\hydrators;

class ValueTextHydrator implements ValueHydratorInterface
{
    function hydrate($valueRow)
    {
        return $valueRow['value_text'];
    }

    function dehytrate($value)
    {
        return $value;
    }
}