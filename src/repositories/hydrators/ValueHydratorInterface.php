<?php

namespace svsoft\yii\items\repositories\hydrators;
interface ValueHydratorInterface
{
    function hydrate($data);

    function dehytrate($value);
}