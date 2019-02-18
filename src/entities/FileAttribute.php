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
    private $filePath;

    function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    function getFilePath()
    {
        return $this->filePath;
    }

    function getFileName()
    {
        return pathinfo($this->filePath, PATHINFO_BASENAME);
    }
}