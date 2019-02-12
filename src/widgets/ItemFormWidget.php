<?php

namespace svsoft\yii\items\widgets;

use svsoft\yii\items\entities\Field;
use svsoft\yii\items\forms\ItemForm;
use yii\base\InvalidCallException;
use yii\widgets\ActiveField;
use yii\widgets\ActiveForm;

class ItemFormWidget extends ActiveForm
{
    /**
     * @var ItemForm
     */
    public $itemForm;


    public $enableClientValidation = false;

    public $fieldConfig = [
        'class'=>\svsoft\yii\items\widgets\ActiveField::class,
    ];

    function init()
    {
        if (!$this->itemForm)
            throw new InvalidCallException('Property itemForm must be set');


        parent::init();
    }

    /**
     * @return ActiveField[]
     */
    function fields()
    {
        $fieldWidgets = [];
        foreach($this->itemForm->itemType->getFields() as $field)
        {
            $fieldWidget = $this->field($this->itemForm, $field->getName());

            switch($field->getType())
            {
                case Field::TYPE_STRING:
                case Field::TYPE_REAL:
                case Field::TYPE_INT:
                    $fieldWidgets[$field->getName()] = $this->fieldString($fieldWidget);
                    break;
                case Field::TYPE_TEXT:
                    $fieldWidgets[$field->getName()] = $this->fieldText($fieldWidget);
                    break;
                case Field::TYPE_FILE:
                    $fieldWidgets[$field->getName()] = $this->fieldFile($field);
                    break;
            }
        }

        return $fieldWidgets;
    }

    /**
     * @param ActiveField $fieldWidget
     *
     * @return ActiveField
     */
    function fieldString(ActiveField $fieldWidget)
    {
        return $fieldWidget->textInput();
    }

    /**
     * @param ActiveField $fieldWidget
     *
     * @return ActiveField
     */
    function fieldText(ActiveField $fieldWidget)
    {
        return $fieldWidget->textarea();
    }

    /**
     * @param ActiveField $fieldWidget
     *
     * @return ActiveField
     */
    function fieldFile(Field $field)
    {
        if (!isset($this->options['enctype']))
            $this->options['enctype'] = 'multipart/form-data';

        return $this->field($this->itemForm, $field->getName())->widget(FileUploadWidget::class, ['multiple'=>$field->getMultiple()]);

    }

}