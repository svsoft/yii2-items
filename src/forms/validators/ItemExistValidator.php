<?php

namespace svsoft\yii\items\forms\validators;

use svsoft\yii\items\forms\ItemForm;
use svsoft\yii\items\services\Items;
use yii\base\InvalidConfigException;
use yii\validators\Validator;
use Yii;

class ItemExistValidator extends Validator
{
    public $message;

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

        $itemType = $model->getItemType();

        if (!$fieldItemTypeId = $itemType->getFieldByName($attribute)->getType()->getParam('itemTypeId'))
            throw new InvalidConfigException("Attribute \"{$attribute}\" must be type of item");

        /** @var Items $items */
        $items = Yii::$container->get(Items::class);

        $fieldItemType = $items->getItemTypeById($fieldItemTypeId);

        $query = $items->itemManager->createQuery($fieldItemType);
        $query->andId($value);

        $exists = $query->exists();

        if (!$exists)
            $this->addError($model, $attribute, $this->message, ['value' => $value]);
    }
}