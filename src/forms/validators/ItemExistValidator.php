<?php

namespace svsoft\yii\items\forms\validators;

use svsoft\yii\items\entities\ItemType;
use svsoft\yii\items\forms\ItemForm;
use yii\base\InvalidConfigException;
use yii\validators\Validator;
use Yii;

class ItemExistValidator extends Validator
{
    public $message;

    /**
     * @var ItemType
     */
    public $itemType;

    public function init()
    {
        if ($this->message === null) {
            $this->message = Yii::t('yii', '{attribute} is invalid.');

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

        if ($model instanceof ItemForm)
        {
            $query = $model->getQuery($attribute);
        }
        else
        {
            throw new \Exception('Not realized');
        }

        $query->andId($value);

        $exists = $query->exists();

        if (!$exists)
            $this->addError($model, $attribute, $this->message, ['value' => $value]);
    }
}