<?php

namespace svsoft\yii\items\forms;

use svsoft\yii\items\entities\Field;
use svsoft\yii\items\entities\Item;
use svsoft\yii\items\entities\ItemType;
use svsoft\yii\items\forms\validators\ExistValidator;
use svsoft\yii\items\forms\validators\UniqueValidator;
use svsoft\yii\items\helpers\PostFiles;
use svsoft\yii\items\repositories\ItemQuery;
use svsoft\yii\items\repositories\ItemTypeRepository;
use svsoft\yii\items\services\ItemManager;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * Class ItemForm
 * @package svsoft\yii\items
 * @property-read ItemType $itemType
 */
class ItemForm extends DynamicModel
{
    /**
     * @var ItemType
     */
    protected $itemType;

    /**
     * @var Item
     */
    protected $item;

    /**
     * @var ItemQuery
     */
    protected $queries;

    function __construct(ItemType $itemType, array $config = [])
    {
        $this->itemType = $itemType;

        $attributes = [];
        foreach ($itemType->getFields() as $field) {
            $attributes[] = $field->getName();
        }

        parent::__construct($attributes, $config);
    }

    /**
     * @throws InvalidConfigException
     * @throws \svsoft\yii\items\exceptions\ItemTypeNotFoundException
     * @throws \yii\di\NotInstantiableException
     */
    public function init()
    {
        foreach ($this->itemType->getFields() as $field) {
            $name = $field->getName();

            if ($field->getType()->getId() === Field::TYPE_ITEM) {
                // Если тип сслыка на элемен, то добавляем запрос допустимых элементов для выбора

                $fieldItemTypeId = $field->getType()->getParam('itemTypeId');

                /** @var ItemTypeRepository $repo */
                $repo = \Yii::$container->get(ItemTypeRepository::class);
                /** @var ItemManager $itemManager */
                $itemManager = \Yii::$container->get(ItemManager::class);

                $fieldItemType = $repo->get($fieldItemTypeId);
                $query = $itemManager->createQuery($fieldItemType);

                $this->queries[$name] = $query;
            }
        }

        parent::init();
    }

    public function rules()
    {
        return $this->defaultRules();
    }

    final protected function defaultRules(): array
    {
        /** @var ItemTypeRepository $repo */
        $repo = \Yii::$container->get(ItemTypeRepository::class);

        $rules = [];
        foreach ($this->itemType->getFields() as $field) {
            $name = $field->getName();

            $rule = [];
            switch ($field->getType()->getId()) {
                case Field::TYPE_STRING :
                    $rule = [$name, 'string', 'max' => 255];
                    break;
                case Field::TYPE_TEXT :
                case Field::TYPE_HTML :
                    $rule = [$name, 'string'];
                    break;
                case Field::TYPE_INT :
                    $rule = [$name, 'integer'];
                    break;
                case Field::TYPE_REAL :
                    $rule = [$name, 'number'];
                    break;
                case Field::TYPE_FILE :
                    $rule = [$name, 'file', 'maxFiles' => $field->getMultiple() ? 10 : 1];
                    break;
                case Field::TYPE_ITEM :
                    $rule = [
                        $name,
                        ExistValidator::class,
                        'targetItemType' => $repo->get($field->getType()->getParam('itemTypeId')),
                        'targetAttribute' => 'id'
                    ];
                    break;
                case Field::TYPE_DATE :
                    $rule = [$name, 'date', 'format' => 'php:Y-m-d'];
                    break;
                case Field::TYPE_DATETIME :
                    $rule = [$name, 'date', 'format' => 'php:Y-m-d H:i:s'];
                    break;
                case Field::TYPE_BOOLEAN :
                    $rule = [$name, 'boolean'];
                    break;

            }

            if ($rule) {
                $rules[$name] = $rule;
            }

            if ($field->getType()->getRequired()) {
                $rules[$name . '-required'] = [$name, 'required'];
            }

            if ($field->getType()->getUnique()) {
                $rules[$name . '-unique'] = [$name, UniqueValidator::class];
            }
        }

        return $rules;
    }

    /**
     * @param $attribute
     * @param bool $clone
     *
     * @return ItemQuery
     * @throws InvalidConfigException
     */
    function getQuery($attribute, $clone = true)
    {
        if (!$query = ArrayHelper::getValue($this->queries, $attribute))
            throw new InvalidConfigException("Attribute \"{$attribute}\" must be type of item");

        if ($clone)
            return clone $query;

        return $query;
    }

    /**
     * @param Item $item
     *
     * @throws \svsoft\yii\items\exceptions\ItemAttributeNotFound
     */
    public function setItem(Item $item)
    {
        $this->item = $item;

        if ($item)
            (new ItemFormFiller())->fill($item, $this);
    }


    /**
     * @return Item
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @return ItemType
     */
    public function getItemType()
    {
        return $this->itemType;
    }

    /**
     * @param $data
     * @param null $formName
     *
     * @return bool
     */
    public function loadFiles($data, $formName = null)
    {
        $attributes = PostFiles::getFiles($data, $formName ?: $this->formName());

        foreach ($attributes as $name => $value) {
            $oldValue = $this->getAttribute($name);

            if (\is_array($oldValue)) {
                $attributes[$name] = array_merge($oldValue, $value);
            } else {
                $attributes[$name] = $value;
            }
        }

        $this->setAttributes($attributes);

        return (bool)$attributes;

    }

    public function getAttribute($name)
    {
        $attributes = $this->getAttributes([$name]);

        return $attributes[$name];
    }

    public function getAttributeLabel($attribute)
    {
        return \Yii::t('items', parent::getAttributeLabel($attribute));
    }
}

