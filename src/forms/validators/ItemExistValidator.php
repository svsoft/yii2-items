<?php

namespace svsoft\yii\items\forms\validators;

use svsoft\yii\items\entities\ItemType;
use svsoft\yii\items\forms\ItemForm;
use svsoft\yii\items\repositories\ItemQuery;
use svsoft\yii\items\services\ItemManager;
use yii\base\InvalidConfigException;
use yii\validators\Validator;
use Yii;

class ItemExistValidator extends Validator
{
    public $targetAttribute;
    /** @var ItemType|string */
    public $targetItemType;
    public $filter;

    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = Yii::t('yii', '{attribute} is invalid.');
        }
    }

    /**
     * @param ItemForm $model
     * @param string $attribute
     *
     * @throws InvalidConfigException
     * @throws \svsoft\yii\items\exceptions\ItemTypeNotFoundException
     * @throws \yii\di\NotInstantiableException
     */
    public function validateAttribute($model, $attribute)
    {
        $targetAttribute = $this->targetAttribute ?? $attribute;

        $targetItemType = $this->getTargetItemType($model);
        $query = $this->createQuery($targetItemType, [$targetAttribute => $model->$attribute]);
        $exists = $query->exists();
        if (!$exists) {
            $this->addError($model, $attribute, $this->message, [
                'targetAttribute' => $targetAttribute
            ]);
        }
    }

    /**
     * @param mixed $value
     * @return array|null
     * @throws InvalidConfigException
     * @throws \svsoft\yii\items\exceptions\ItemTypeNotFoundException
     * @throws \yii\di\NotInstantiableException
     */
    protected function validateValue($value)
    {
        if ($this->targetItemType === null) {
            throw new InvalidConfigException('The "targetItemType" property must be set.');
        }
        if (!\is_string($this->targetAttribute)) {
            throw new InvalidConfigException('The "targetAttribute" property must be configured as a string.');
        }

        $query = $this->createQuery($this->targetItemType, [$this->targetAttribute => $value]);

        return $query->exists() ? null : [$this->message, []];
    }

    /**
     * @param $model
     * @return string|ItemType
     */
    private function getTargetItemType($model)
    {
        return $this->targetItemType ?? $model->itemType;
    }

    /**
     * @param ItemType|string $itemType
     * @param $condition
     * @return ItemQuery
     * @throws InvalidConfigException
     * @throws \svsoft\yii\items\exceptions\ItemTypeNotFoundException
     * @throws \yii\di\NotInstantiableException
     */
    private function createQuery($itemType, $condition): ItemQuery
    {
        /** @var ItemManager $itemManager */
        $itemManager = \Yii::$container->get(ItemManager::class);
        $query = $itemManager->createQuery($itemType)->andWhere($condition);
        if ($this->filter instanceof \Closure) {
            \call_user_func($this->filter, $query);
        } elseif ($this->filter !== null) {
            $query->andWhere($this->filter);
        }

        return $query;
    }
}