<?php
/**
 * Created by PhpStorm.
 * User: viktor
 * Date: 30.01.2019
 * Time: 16:15
 */

namespace svsoft\yii\items\entities;

class AbstractFileAttribute implements FileAttributeInterface
{
    protected $filename;

    function getFilePath()
    {
        return null;
    }

    function getFileName()
    {
        return $this->filename;
    }

    function getExtension()
    {
        return pathinfo($this->filename, PATHINFO_EXTENSION);
    }
}