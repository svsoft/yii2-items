<?php

namespace svsoft\yii\items\repositories;


use svsoft\yii\items\exceptions\FileStorageException;
use yii\db\Connection;

class FileStorage
{
    private $dirPath;

    private $chmod = 0664;

    /**
     * FileStorage constructor.
     *
     * @param Connection $db
     * @param $dirPath
     *
     * @throws FileStorageException
     */
    public function __construct($dirPath)
    {
        $this->dirPath = $dirPath;

        if (!$this->dirPath)
            throw new FileStorageException("Param dirPath is not set");

        if (!file_exists($this->dirPath))
            throw new FileStorageException("Directory " . $this->dirPath . " does not exist");
        elseif (!is_dir($this->dirPath))
            throw new FileStorageException("Path " . $this->dirPath . " is not directory");
        elseif (!is_writable($this->dirPath))
            throw new FileStorageException("Can not write to directory " . $this->dirPath);
    }

    /**
     * @param $filename
     *
     * @return string
     * @throws FileStorageException
     */
    public function getPath($filename)
    {
        if (!$filename)
            throw new FileStorageException("Param filename is not set");

        return $this->dirPath . DIRECTORY_SEPARATOR . $filename;
    }

    /**
     * @param $filePath
     * @param $filename
     *
     * @throws FileStorageException
     */
    public function saveFile($filename, $filePath)
    {
        $path = $this->getPath($filename);
        if (!copy($filePath, $path))
            throw new FileStorageException("Error save file");

        chmod($path, $this->chmod);
    }

    /**
     * @param $filePath
     * @param $filename
     *
     * @throws FileStorageException
     */
    public function saveUploadedFile($filename, $filePath)
    {
        $path = $this->getPath($filename);
        if (!move_uploaded_file($filePath, $this->getPath($filename)))
            throw new FileStorageException("Error save file");

        chmod($path, $this->chmod);
    }

    public function fileExist($filename)
    {
        return file_exists($this->getPath($filename));
    }

    public function deleteFile($filename)
    {
        $path = $this->getPath($filename);

        if (!file_exists($path))
            throw new FileStorageException("File \"$path\" does not exist");

        if (!is_file($path))
            throw new FileStorageException($path . ' is not file');

        if (!is_writable($path))
            throw new FileStorageException('Permission deny for deleting file ' . $path);

        if (!unlink($path))
            throw new FileStorageException("Error delete file");
    }
}