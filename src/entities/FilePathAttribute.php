<?php
/**
 * Created by PhpStorm.
 * User: viktor
 * Date: 30.01.2019
 * Time: 16:15
 */

namespace svsoft\yii\items\entities;

class FilePathAttribute extends AbstractFileAttribute
{
    private $filePath;

    function __construct($filePath)
    {
        $this->filePath = $filePath;
        $this->filename = md5($filePath . '_' . filesize($filePath)). '.' .pathinfo($this->filePath, PATHINFO_EXTENSION);
    }

    function getFilePath()
    {
        return $this->filePath;
    }
}