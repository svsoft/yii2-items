<?php
/**
 * Created by PhpStorm.
 * User: viktor
 * Date: 30.01.2019
 * Time: 16:15
 */

namespace svsoft\yii\items\entities;

class FileAttribute extends AbstractFileAttribute
{
    function __construct($filename)
    {
        $this->filename = $filename;
    }
}