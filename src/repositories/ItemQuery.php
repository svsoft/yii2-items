<?php

namespace svsoft\yii\items\repositories;

use svsoft\yii\items\entities\Field;
use svsoft\yii\items\entities\Item;
use svsoft\yii\items\entities\ItemType;
use svsoft\yii\items\exceptions\FieldNotFoundException;
use svsoft\yii\items\repositories\hydrators\ItemHydrator;
use svsoft\yii\items\repositories\tables\TableItem;
use svsoft\yii\items\repositories\tables\TableValue;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class ItemQuery extends Query
{
    /**
     * @var ItemType
     */
    protected $itemType;

    /**
     * @var TableManager
     */
    protected $tableManager;

    /**
     * @var ItemHydrator
     */
    protected $itemHydrator;


    private $fieldNames = [];

    function __construct(ItemType $itemType, TableManager $tableManager, ItemHydrator $itemHydrator)
    {
        $this->tableManager = $tableManager;
        $this->itemType = $itemType;
        $this->itemHydrator = $itemHydrator;

        parent::__construct([]);
    }


    /**
     * @param \yii\db\QueryBuilder $builder
     *
     * @return Query
     */
    function prepare($builder)
    {
        $itemTypeKey = $this->tableManager->getTableItemType()->getKey($this->itemType->getId());
        $itemQuery = $this->tableManager->getTableItem()->query()
            ->from(['item'=>TableItem::TABLE])
            ->select(['item.*'])
            ->andWhere(['item_type_key'=>$itemTypeKey]);

        $this->parseWhere($this->where);
        $this->parseOrderBy();

        // Получаем поля типа, индексированные по названию
        /** @var Field[] $fields */
        $fields = ArrayHelper::index($this->itemType->getFields(), function (Field $field) { return $field->getName(); });

        // Добавляем джойны для фильтрации и сортировки
        foreach(array_unique($this->fieldNames) as $fieldName)
        {
            if ($fieldName=='id')
                continue;

            if (empty($fields[$fieldName]))
                throw new FieldNotFoundException("Field \"$fieldName\" not found");

            $field = $fields[$fieldName];

            $fieldKey = $this->tableManager->getTableField()->getKey($field->getId());
            $tableAlias = 'join_'.$fieldName;
            $column = $this->tableManager->getTableValue()->getValueColumn($field->getType());
            $itemQuery->addSelect(["{$tableAlias}.{$column} as $fieldName"]);
            $itemQuery->leftJoin([$tableAlias=>TableValue::TABLE], "{$tableAlias}.item_key = item.key AND $tableAlias.field_key = $fieldKey");
        }
        $this->select(['id','key','item_type_key']);
        $this->from = ['q'=>$itemQuery];
        //$this->groupBy(['id','key','item_type_key']);

        return parent::prepare($builder);
    }

    /**
     * Парсит условия получения списка названий полей
     * @param $condition
     */
    private function parseWhere($condition)
    {
        if (!$condition)
            return;

        if (array_key_exists(0, $condition))
        {
            foreach($condition as $conditionItem)
            {
                if (is_array($conditionItem))
                {
                    $this->parseWhere($conditionItem);
                }
            }

            if (!is_array($condition[1]))
                $this->fieldNames[] = $condition[1];

        }
        else
        {
            foreach($condition as $field=>$value)
            {
                $this->fieldNames[] = $field;
            }
        }
    }

    private function parseOrderBy()
    {
        if (!$this->orderBy)
            return;

        foreach($this->orderBy as $field=>$order)
            $this->fieldNames[] = $field;
    }


    /**
     * @param array $rows
     *
     * @return Item[]
     */
    function populate($rows)
    {
        $itemRows = [];
        foreach ($rows as $row)
            $itemRows[$row['key']] = $row;

        $itemKeys = array_keys($itemRows);

        $valueRows = $this->tableManager->getTableValue()->query()->andWhere(['item_key' => $itemKeys])->all();

        foreach($valueRows as $valueRow)
        {
            $itemKey = $valueRow['item_key'];
            $itemRows[$itemKey]['values'][] = $valueRow;
        }

        /** @var Item[] $items */
        $items = [];
        foreach($itemRows as $itemRow)
        {
            $item = $this->itemHydrator->hydrate($itemRow);

            $items[] = $item;
        }

        if ($this->indexBy === null) {
            return $items;
        }

        $result = [];
        foreach ($items as $item) {
            $result[$item->getProperty($this->indexBy)] = $item;
        }

        return $result;
    }

    /**
     * @param $id
     *
     * @return ItemQuery
     */
    function andId($id)
    {
        return $this->andWhere(['id'=>$id]);
    }

    function andNotId($id)
    {
        return $this->andWhere(['<>','id',$id]);
    }
}