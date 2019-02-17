<?php

namespace svsoft\yii\items\factories;

use svsoft\yii\items\entities\types\FieldType;

class FieldTypeFactory
{
    function __construct()
    {
    }

    function build($id, $params = [])
    {
        return new FieldType($id, $params);
    }
}