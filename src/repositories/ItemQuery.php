<?php

namespace svsoft\yii\items\repositories;

use svsoft\yii\items\entities\Field;
use svsoft\yii\items\entities\Item;
use svsoft\yii\items\entities\ItemType;
use svsoft\yii\items\exceptions\FieldNotFoundException;
use svsoft\yii\items\repositories\hydrators\ItemHydrator;
use svsoft\yii\items\repositories\tables\TableItem;
use svsoft\yii\items\repositories\tables\TableValue;
use svsoft\yii\items\services\Cacher;
use yii\db\Connection;
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

    /**
     * @var Cacher
     */
    protected $cacher;

    /**
     * @var bool
     */
    protected $useCache;

    function __construct(ItemType $itemType, TableManager $tableManager, ItemHydrator $itemHydrator, Cacher $cacher)
    {
        $this->tableManager = $tableManager;
        $this->itemType = $itemType;
        $this->itemHydrator = $itemHydrator;
        $this->cacher = $cacher;

        $this->useCache = false;

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
            $column = $this->tableManager->getTableValue()->getValueColumn($field->getType()->getId());
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
        if (!$this->useCache)
            return $this->populateInternal($rows);

        $cacheKey = md5(serialize($this));
        if (($result = $this->cacher->get($cacheKey))===false)
        {
            $result = $this->populateInternal($rows);
            $this->cacher->set($cacheKey, $result, $this->itemType->getId());
        }

        return $result;
    }

    /**
     * @param $rows
     *
     * @return Item[]
     */
    protected function populateInternal($rows)
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
            $items[] = $this->itemHydrator->hydrate($itemRow);

        if ($this->indexBy === null)
            return $items;

        $result = [];
        foreach ($items as $item)
            $result[$item->getProperty($this->indexBy)] = $item;

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

    /**
     * @param null $db
     *
     * @return array|Item[]
     */
    function all($db = null)
    {
        return parent::all($db); // TODO: Change the autogenerated stub
    }

    /**
     * @param null $db
     *
     * @return array|Item
     */
    function one($db = null)
    {
        $row = parent::one($db);
        if ($row !== false) {
            $models = $this->populate([$row]);
            return reset($models) ?: null;
        }

        return null;
    }

    /**
     * Queries a scalar value by setting [[select]] first.
     * Restores the value of select to make this query reusable.
     * @param string|\yii\db\ExpressionInterface $selectExpression
     * @param Connection|null $db
     * @return bool|string
     */
    protected function queryScalar($selectExpression, $db)
    {
        if ($this->emulateExecution) {
            return null;
        }

        $command = (new Query())
            ->select([$selectExpression])
            ->from(['c' => $this])
            ->createCommand($db);
        $this->setCommandCache($command);

        return $command->queryScalar();
    }

}