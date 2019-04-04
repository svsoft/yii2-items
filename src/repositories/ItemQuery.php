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
     * Флаг кеширования результатов
     *
     * @var bool
     */
    protected $cacheResult;

    /**
     * ItemQuery constructor.
     *
     * @param $itemType
     * @param TableManager $tableManager
     * @param ItemHydrator $itemHydrator
     * @param Cacher $cacher
     * @param ItemTypeRepository $itemTypeRepository
     *
     * @throws \svsoft\yii\items\exceptions\ItemTypeNotFoundException
     */
    function __construct($itemType, TableManager $tableManager, ItemHydrator $itemHydrator, Cacher $cacher, ItemTypeRepository $itemTypeRepository)
    {
        if (!$itemType instanceof ItemType)
            $itemType = $itemTypeRepository->getByName($itemType);

        $this->tableManager = $tableManager;
        $this->itemType = $itemType;
        $this->itemHydrator = $itemHydrator;
        $this->cacher = $cacher;

        $this->cacheResult = true;

        parent::__construct([]);
    }


    /**
     * @param \yii\db\QueryBuilder $builder
     *
     * @return Query
     * @throws FieldNotFoundException
     */
    function prepare($builder)
    {
        $itemTypeKey = $this->tableManager->getTableItemType()->getKey($this->itemType->getId());
        $itemQuery = $this->tableManager->getTableItem()->query()
            ->from(['item'=>TableItem::TABLE])
            ->select(['item.*'])
            ->andWhere(['item_type_key'=>$itemTypeKey]);

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

    public function andWhere($condition, $params = [])
    {
        $this->parseCondition($condition);

        return parent::andWhere($condition, $params);
    }

    public function orWhere($condition, $params = [])
    {
        $this->parseCondition($condition);

        return parent::orWhere($condition, $params);
    }

    public function where($condition, $params = [])
    {
        $this->parseCondition($condition);
        return parent::where($condition, $params); // TODO: Change the autogenerated stub
    }

    /**
     * @param array|string|\yii\db\ExpressionInterface $columns
     *
     * @return Query|$this
     */
    public function orderBy($columns)
    {
        $this->addFieldNames($columns);

        return parent::orderBy($columns);
    }

    /**
     * @param array|string|\yii\db\ExpressionInterface $columns
     *
     * @return $this|Query
     */
    public function addOrderBy($columns)
    {
        $this->addFieldNames($columns);
        return parent::addOrderBy($columns);
    }

    /**
     * Парсит условия получения списка названий полей
     * @param $condition
     */
    private function parseCondition($condition)
    {
        if (!$condition)
            return;

        if (array_key_exists(0, $condition))
        {
            foreach($condition as $conditionItem)
            {
                if (is_array($conditionItem))
                {
                    $this->parseCondition($conditionItem);
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

    private function addFieldNames($columns)
    {
        foreach($columns as $field=>$order)
            $this->fieldNames[] = $field;
    }

    /**
     * @param $rows
     *
     * @return Item[]
     */
    public function populate($rows)
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
     * @param $value
     *
     * @return $this
     */
    function cacheResult($value)
    {
        $this->cacheResult = (bool)$value;

        return $this;
    }

    /**
     * @param $id
     *
     * @return ItemQuery|Query
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
        if (!$this->cacheResult)
            return parent::all($db);

        $cacheKey = md5(serialize($this));
        if (($result = $this->cacher->get($cacheKey))===false)
        {
            $result = parent::all($db);
            $this->cacher->set($cacheKey, $result, $this->itemType->getId());
        }

        return $result;
    }

    /**
     * @param null $db
     *
     * @return array|Item
     */
    function one($db = null)
    {
        $this->limit(1);
        $all = $this->all();

        if (!$all)
            return null;

        reset($all);
        return current($all);
    }

    /**
     * @param string|\yii\db\ExpressionInterface $selectExpression
     * @param null|Connection $db
     *
     * @return bool|false|null|string
     * @throws \yii\db\Exception
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