<?php

namespace svsoft\yii\items\repositories\hydrators;

use svsoft\yii\items\entities\FileAttribute;
use svsoft\yii\items\entities\FileAttributeInterface;

class ValueFileHydrator implements ValueHydratorInterface
{
    function hydrate($value)
    {
        return $value ? new FileAttribute($value) : null;
    }

    /**
     * @param FileAttributeInterface $value
     *
     * @return array
     */
    function dehytrate($value)
    {
        return $value === null ? null : $value->getFileName();
    }
}