<?php

namespace svsoft\yii\items\forms\validators;

use svsoft\yii\items\forms\ItemForm;
use svsoft\yii\items\repositories\ItemQuery;
use yii\base\InvalidConfigException;
use yii\validators\Validator;
use Yii;

class UniqueValidator extends Validator
{
    public $message;

    public function init()
    {
        if ($this->message === null) {
            $this->message = Yii::t('yii', '{attribute} "{value}" has already been taken.');
        }

        parent::init();
    }

    /**
     * @param ItemForm $model
     * @param string $attribute
     *
     * @throws InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;

        $itemType = $model->getItemType();

        $query = Yii::$container->get(ItemQuery::class,[$itemType]);
        $query->andWhere([$attribute=>$value]);
        if ($item = $model->getItem())
            $query->andNotId($item->getId());

        $exists = $query->exists();

        if ($exists)
            $this->addError($model, $attribute, $this->message, ['value' => $value]);
    }
}