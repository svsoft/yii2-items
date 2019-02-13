<?php

namespace svsoft\yii\items\repositories;

use svsoft\yii\items\entities\AbstractFileAttribute;
use svsoft\yii\items\entities\FileAttributeInterface;
use svsoft\yii\items\entities\FilePathAttribute;
use svsoft\yii\items\entities\Item;
use svsoft\yii\items\entities\Field;
use svsoft\yii\items\entities\UploadedFileAttribute;
use svsoft\yii\items\repositories\hydrators\ItemHydrator;
use yii\db\Connection;

class ItemRepository
{
    /**
     * @var ItemHydrator
     */
    private $itemHydrator;

    /**
     * @var ItemTypeRepository
     */
    private $itemTypeRepository;

    /**
     * @var TableManager
     */
    private $tableManager;

    /**
     * @var FileStorage
     */
    private $fileStorage;

    /**
     * ItemRepository constructor.
     *
     * @param TableManager $tableManager
     * @param ItemHydrator $itemHydrator
     * @param ItemTypeRepository $itemTypeRepository
     * @param FileStorage $fileStorage
     */
    function __construct(TableManager $tableManager, ItemHydrator $itemHydrator, ItemTypeRepository $itemTypeRepository, FileStorage $fileStorage)
    {
        $this->tableManager = $tableManager;

        $this->itemHydrator = $itemHydrator;

        $this->itemTypeRepository = $itemTypeRepository;

        $this->fileStorage = $fileStorage;
    }

    function create(Item $item)
    {
        $t = $this->tableManager->beginTransaction();
        try
        {
            $itemData = $this->itemHydrator->dehytrate($item);

            $columns = $itemData;
            unset($columns['values']);
            $this->tableManager->getTableItem()->insert($columns);
            $itemKey = $this->tableManager->getDb()->getLastInsertID();

            foreach($itemData['values'] as $valueRow)
            {
                $valueRow['item_key'] = $itemKey;
                $this->tableManager->getTableValue()->insert($valueRow);
            }

            $t->commit();
        }
        catch(\Exception $exception)
        {
            $t->rollBack();
            throw $exception;
        }
        catch(\Throwable $exception)
        {
            $t->rollBack();
            throw $exception;
        }
    }

    function get($id)
    {
        $itemRow = $this->tableManager->getTableItem()->query()->andWhere(['id' =>$id])->one();
        $itemKey = $itemRow['key'];
        $valueRows = $this->tableManager->getTableValue()->query()->andWhere(['item_key' =>$itemKey])->all();
        $itemData = $itemRow;
        $itemData['values'] = $valueRows;

        return $this->itemHydrator->hydrate($itemData);
    }

    private function getValueAsArray($value)
    {
        $values = [];
        if (is_array($value))
        {
            $values = $value;
        }
        else
        {
            if ($value !== null)
                $values = [$value];
        }

        return $values;
    }

    /**
     * @param $value
     *
     * @return FileAttributeInterface[]
     */
    private function getFileValueAsArray($value)
    {
        /** @var AbstractFileAttribute[] $values */
        $values = $this->getValueAsArray($value);

        /** @var AbstractFileAttribute[] $files */
        $files = [];
        foreach($values as $value)
        {
            if ($value instanceof AbstractFileAttribute)
                $files[$value->getFileName()] = $value;
        }

        return $files;
    }

    function update(Item $item)
    {
        $oldItem = $this->get($item->getId());

        $t = $this->tableManager->beginTransaction();
        try
        {
            $itemData = $this->itemHydrator->dehytrate($item);
            $itemKey = $itemData['key'];

            $this->tableManager->getTableValue()->delete(['item_key' =>$itemKey]);

            foreach($itemData['values'] as $valueRow)
            {
                $this->tableManager->getTableValue()->insert($valueRow);
            }

            $itemType = $this->itemTypeRepository->get($item->getItemTypeId());
            foreach($itemType->getFields() as $field)
            {
                $attribute = $field->getName();

                if ($field->getType() === Field::TYPE_FILE)
                {
                    $files = $this->getFileValueAsArray($item->getAttribute($attribute));
                    $oldFiles = $this->getFileValueAsArray($oldItem->getAttribute($attribute));

                    foreach($files as $filename=>$file)
                    {
                        if (empty($oldFiles[$filename]))
                        {
                            if ($file instanceof FilePathAttribute)
                            {
                                $this->fileStorage->saveFile($file->getFileName(), $file->getFilePath());
                            }
                            elseif ($file instanceof UploadedFileAttribute)
                            {
                                $this->fileStorage->saveFile($file->getFileName(), $file->getFilePath());
                            }

                            // todo: Тут нужно что то придумать чтоб при повторном событии EVENT_ROLLBACK_TRANSACTION обработчик не срабатывал
                            $this->tableManager->getDb()->on(Connection::EVENT_ROLLBACK_TRANSACTION, function () use ($file){
                                if ($this->fileStorage->fileExist($file->getFileName()))
                                    $this->fileStorage->deleteFile($file->getFileName());
                            });
                        }
                    }

                    foreach($oldFiles as $filename=>$file)
                    {

                        if (empty($files[$filename]))
                        {
                            // todo: Тут нужно что то придумать чтоб при повторном событии EVENT_COMMIT_TRANSACTION обработчик не срабатывал
                            $this->tableManager->getDb()->on(Connection::EVENT_COMMIT_TRANSACTION, function () use ($file){
                                if ($this->fileStorage->fileExist($file->getFileName()))
                                    $this->fileStorage->deleteFile($file->getFileName());
                            });

                        }
                    }

                }
            }

            $t->commit();
        }
        catch(\Exception $exception)
        {
            $t->rollBack();
            throw $exception;
        }
        catch(\Throwable $exception)
        {
            $t->rollBack();
            throw $exception;
        }

    }

    function delete(Item $item)
    {
        $t = $this->tableManager->beginTransaction();
        try
        {
            $itemKey = $this->tableManager->getTableItem()->getKey($item->getId());
            $this->tableManager->getTableValue()->delete(['item_key' =>$itemKey]);
            $this->tableManager->getTableItem()->delete(['key'=>$itemKey]);

            $itemType = $this->itemTypeRepository->get($item->getItemTypeId());
            foreach($itemType->getFields() as $field)
            {
                if ($field->getType() === Field::TYPE_FILE)
                {
                    $attribute = $field->getName();
                    $files = $this->getFileValueAsArray($item->getAttribute($attribute));

                    // todo: Тут нужно что то придумать чтоб при повторном событии EVENT_COMMIT_TRANSACTION обработчик не срабатывал
                    $this->tableManager->getDb()->on(Connection::EVENT_COMMIT_TRANSACTION, function () use ($files){
                        foreach($files as $filename=>$file)
                        {
                            if ($this->fileStorage->fileExist($file->getFileName()))
                                $this->fileStorage->deleteFile($file->getFileName());
                        }
                    });
                }
            }

            $t->commit();
        }
        catch(\Exception $exception)
        {
            $t->rollBack();
            throw $exception;
        }
        catch(\Throwable $exception)
        {
            $t->rollBack();
            throw $exception;
        }

    }
}