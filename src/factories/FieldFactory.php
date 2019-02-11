<?php

namespace svsoft\yii\items\factories;

use svsoft\yii\items\entities\Field;

class FieldFactory
{
    private function generateId()
    {
        return uniqid(bin2hex(random_bytes(1)));
    }

    function build($name, $type, $multiple)
    {
        return new Field($this->generateId(), $name, $type, $multiple);
    }

}