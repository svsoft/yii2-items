<?php
/**
 * Created by PhpStorm.
 * User: viktor
 * Date: 21.01.2019
 * Time: 15:17
 */

namespace svsoft\yii\items\repositories;

use svsoft\yii\items\entities\ItemType;
use svsoft\yii\items\exceptions\ItemTypeNotFoundException;
use svsoft\yii\items\repositories\hydrators\ItemTypeHydrator;
use yii\db\Connection;
use yii\helpers\ArrayHelper;

class ItemTypeRepository
{
    /**
     * @var Connection
     */
    private $db;

    /**
     * @var ItemTypeHydrator
     */
    private $itemTypeHydrator;

    /**
     * @var TableManager
     */
    private $tableManager;

    /**
     * @var ItemType[]
     */
    private $_itemTypes;

    private $_itemTypeIndex;

    function __construct(Connection $db, TableManager $tableManager, ItemTypeHydrator $itemTypeHydrator)
    {
        $this->db = $db;
        $this->itemTypeHydrator = $itemTypeHydrator;
        $this->tableManager = $tableManager;
    }


    /**
     * @param ItemType $itemType
     *
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    function create(ItemType $itemType)
    {
        $data = $this->itemTypeHydrator->dehytrate($itemType);

        $t = $this->db->beginTransaction();
        try
        {
            $columns = ArrayHelper::filter($data,['id','name']);


            $this->tableManager->getTableItemType()->insert($columns);
            $itemTypeKey = $this->db->getLastInsertID();

            foreach($data['fields'] as $fieldData)
            {
                $this->createField($itemTypeKey, $fieldData);
            }

            $t->commit();
            $this->resetInternalRepository();
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

    private function createField($itemTypeKey, $data)
    {
        $columns = $data;
        $columns['item_type_key'] = $itemTypeKey;
        $this->tableManager->getTableField()->insert($columns);
    }

    /**
     * @param $id
     *
     * @return ItemType
     * @throws ItemTypeNotFoundException
     */
    function get($id)
    {
        $itemTypes = $this->getItemTypes();
        if (empty($itemTypes[$id]))
            throw new ItemTypeNotFoundException('Item type with id "'.$id.'" not found');

        return clone $itemTypes[$id];
    }

    /**
     * @param $name
     *
     * @return ItemType
     * @throws ItemTypeNotFoundException
     */
    function getByName($name)
    {
        $itemTypes = $this->getItemTypes();

        if (empty($this->_itemTypeIndex[$name]))
            throw new ItemTypeNotFoundException('Item type with name "'.$name.'" not found');

        $id = $this->_itemTypeIndex[$name];

        return clone $itemTypes[$id];
    }

    /**
     * @return ItemType[]
     */
    function getAll()
    {
        $itemTypes = [];
        foreach($this->getItemTypes() as $key=>$itemType)
        {
            $itemTypes[$key] = clone $itemType;
        }

        return $itemTypes;
    }

    private function resetInternalRepository()
    {
        unset($this->_itemTypes);
        unset($this->_itemTypeIndex);
    }

    /**
     * @return ItemType[]
     */
    private function getItemTypes()
    {
        if ($this->_itemTypes === null)
        {
            $this->_itemTypes = $this->getAllFromDb();
            $this->_itemTypeIndex = [];
            foreach($this->_itemTypes as $itemType)
            {
                $this->_itemTypeIndex[$itemType->getName()] = $itemType->getId();
            }
        }

        return $this->_itemTypes;
    }

    /**
     * @return ItemType[]
     */
    private function getAllFromDb()
    {
        $itemTypeRows = $this->tableManager->getTableItemType()->query()->indexBy('key')->all();

        $fieldRows = $this->tableManager->getTableField()->query()->all();

        foreach($fieldRows as $fieldRow)
        {
            $itemTypeKey = $fieldRow['item_type_key'];
            $itemTypeRows[$itemTypeKey]['fields'][] = $fieldRow;
        }

        $itemTypes = [];
        foreach($itemTypeRows as $itemTypeRow)
        {
            $itemType = $this->itemTypeHydrator->hydrate($itemTypeRow);
            $itemTypes[$itemType->getId()] = $itemType;
        }

        return $itemTypes;
    }

    /**
     * @param ItemType $itemType
     *
     * @throws ItemTypeNotFoundException
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    function update(ItemType $itemType)
    {
        $oldItemType = $this->get($itemType->getId());

        $t = $this->db->beginTransaction();

        $data = $this->itemTypeHydrator->dehytrate($itemType);

        try
        {
            $columns = ArrayHelper::filter($data, ['name']);

            $this->tableManager->getTableItemType()->update($columns, ['id' => $data['id']]);
            //$this->db->createCommand()->update(TableItemType::TABLE, $columns, ['id' => $data['id']])->execute();

            foreach($data['fields'] as $fieldData)
            {
                $fieldId = $fieldData['id'];
                $fieldKey = $fieldData['key'];

                $field = $itemType->getField($fieldId);

                if ($oldItemType->hasField($fieldId))
                {
                    $oldField = $oldItemType->getField($fieldId);

                    $this->tableManager->getTableField()->update($fieldData, ['id'=>$fieldId]);

                    if ($oldField->getType() != $field->getType())
                    {
                        $this->tableManager->getTableValue()->changeType($fieldKey, $oldField->getType(), $field->getType());
                    }
                }
                else
                {
                    $this->createField($data['key'], $fieldData);
                }
            }

            // Удаляем поля
            foreach($oldItemType->getFields() as $field)
            {
                if (!$itemType->hasField($field->getId()))
                {
                    $fieldKey = $this->tableManager->getTableField()->getKey($field->getId());

                    $this->tableManager->getTableField()->delete(['id' =>$field->getId()]);
                    $this->tableManager->getTableValue()->delete(['field_key'=>$fieldKey]);
                }
            }

            $t->commit();

            $this->resetInternalRepository();
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