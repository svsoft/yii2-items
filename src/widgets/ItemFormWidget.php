<?php

namespace svsoft\yii\items\widgets;

use dosamigos\ckeditor\CKEditor;
use mihaildev\elfinder\ElFinder;
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
                case Field::TYPE_HTML:
                    $fieldWidgets[$field->getName()] = $this->fieldHtml($field, $fieldWidget);
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

    /**
     * @param Field $field
     * @param ActiveField $activeField
     *
     * @return ActiveField
     */
    function fieldHtml(Field $field, ActiveField $activeField)
    {
        return $activeField->widget(CKEditor::class,[
            'options' => ['rows' => 6],
            'preset' => 'full',
            'clientOptions'=>ElFinder::ckeditorOptions(['elfinder']) + [
                    'allowedContent' => 'a pre blockquote img em p i h1 h2 h3 h4 h5 iframe[*]; div span table tbody thead tr th td ul li ol(*)[*]; br hr strong;',
                    'height'=>250
                ]
        ]);
    }

}