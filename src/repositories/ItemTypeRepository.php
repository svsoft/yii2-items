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
use svsoft\yii\items\repositories\tables\TableField;
use svsoft\yii\items\repositories\tables\TableItemType;
use yii\db\Connection;
use yii\db\Query;
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
        $data = (new Query())->from(TableItemType::TABLE)->andWhere(['id' =>$id])->one();

        if (!$data)
            throw new ItemTypeNotFoundException('Item type with id "'.$id.'" not found');

        $data['fields'] = (new Query())->from(TableField::TABLE)->andWhere(['item_type_key' => $data['key']])->all();;

        $itemType = $this->itemTypeHydrator->hydrate($data);

        return $itemType;
    }

    /**
     * Todo: Перделать, дублирование кода с методом get
     *
     * @param $name
     *
     * @return ItemType
     */
    function getByName($name)
    {
        $data = (new Query())->from(TableItemType::TABLE)->andWhere(['name' =>$name])->one();

        if (!$data)
            return null;

        $data['fields'] = (new Query())->from(TableField::TABLE)->andWhere(['item_type_key' => $data['key']])->all();;

        $itemType = $this->itemTypeHydrator->hydrate($data);

        return $itemType;
    }

    /**
     * @param ItemType $itemType
     *
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