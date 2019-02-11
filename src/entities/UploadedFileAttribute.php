<?php
/**
 * Created by PhpStorm.
 * User: viktor
 * Date: 30.01.2019
 * Time: 16:15
 */

namespace svsoft\yii\items\entities;

use yii\web\UploadedFile;

class UploadedFileAttribute extends AbstractFileAttribute
{
    /**
     * @var UploadedFile
     */
    private $uploadedFile;

    function __construct(UploadedFile $file)
    {
        $this->uploadedFile = $file;
        $this->filename = md5(serialize($file)). '.' .pathinfo($file->name, PATHINFO_EXTENSION);
    }

    /**
     * @return UploadedFile
     */
    function getUploadedFile()
    {
        return $this->uploadedFile;
    }

    function getFilePath()
    {
        return $this->uploadedFile->tempName;
    }
}