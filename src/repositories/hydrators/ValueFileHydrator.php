<?php

namespace svsoft\yii\items\repositories\hydrators;

use svsoft\yii\items\entities\FileAttribute;
use svsoft\yii\items\entities\FileAttributeInterface;
use svsoft\yii\items\repositories\FileStorage;

class ValueFileHydrator implements ValueHydratorInterface
{

    /**
     * @var FileStorage
     */
    protected $fileStorage;

    function __construct(FileStorage $fileStorage)
    {
        $this->fileStorage = $fileStorage;
    }


    function hydrate($value)
    {
        return $value ? new FileAttribute($this->fileStorage->getPath($value)) : null;
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