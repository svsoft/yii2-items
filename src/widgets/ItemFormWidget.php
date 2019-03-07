<?php

namespace svsoft\yii\items\widgets;

use dosamigos\ckeditor\CKEditor;
use mihaildev\elfinder\ElFinder;
use svsoft\yii\items\entities\Field;
use svsoft\yii\items\entities\FileAttribute;
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

    public $labels;

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
            $fieldName = $field->getName();
            $fieldWidget = $this->field($this->itemForm, $field->getName());

            if (isset($this->labels[$fieldName]))
                $fieldWidget->label($this->labels[$fieldName]);

            switch($field->getType()->getId())
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
                    $fieldWidgets[$field->getName()] = $this->fieldFile($fieldWidget, $field);
                    break;
                case Field::TYPE_HTML:
                    $fieldWidgets[$field->getName()] = $this->fieldHtml($field, $fieldWidget);
                    break;
                case Field::TYPE_ITEM:
                    $fieldWidgets[$field->getName()] = $this->fieldItem($field, $fieldWidget);
                    break;
                case Field::TYPE_DATE:
                    $fieldWidgets[$field->getName()] = $this->fieldDate($field, $fieldWidget);
                    break;
                case Field::TYPE_DATETIME:
                    $fieldWidgets[$field->getName()] = $this->fieldDatetime($field, $fieldWidget);
                    break;
                case Field::TYPE_BOOLEAN:
                    $fieldWidgets[$field->getName()] = $this->fieldBoolean($field, $fieldWidget);
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
    function fieldFile(ActiveField $fieldWidget, Field $field)
    {
        if (!isset($this->options['enctype']))
            $this->options['enctype'] = 'multipart/form-data';

        $files = [];
        if ($item = $this->itemForm->getItem())
        {
            $value = $item->getAttribute($field->getName());

            $values = is_array($value) ? $value : [$value];
            foreach($values as $value)
            {
                if ($value instanceof  FileAttribute)
                    $files[$value->getFileName()] = $value->getFilePath();
            }
        }

        return $fieldWidget->widget(FileUploadWidget::class, ['multiple'=>$field->getMultiple(),'files' => $files]);
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

    function fieldItem(Field $field, ActiveField $activeField)
    {
        $query = $this->itemForm->getQuery($field->getName());

        $items = $query->all();

        $list = [];
        if (!$field->getType()->getRequired())
            $list[null] = \Yii::t('yii', '(not set)');

        foreach($items as $item)
        {
            $list[$item->getId()] = (string)$item;
        }

        return $activeField->dropDownList($list, ['multiple'=>$field->getMultiple()]);
    }

    function fieldDate(Field $field, ActiveField $activeField)
    {
        return $activeField->widget(\kartik\widgets\DatePicker::class,[
            'layout' => '{picker}{input}',
            'pluginOptions' => [
                'autoclose'=>true,

                'format' => 'yyyy-mm-dd',
//                'minViewMode'=> 'months',
//                'format' => 'yyyy-mm',
            ]
        ]);
    }

    function fieldDatetime(Field $field, ActiveField $activeField)
    {
        return $activeField->widget(\kartik\widgets\DateTimePicker::class,[
            'layout' => '{picker}{input}',
            'pluginOptions' => [
                'autoclose'=>true,
                'format' => 'yyyy-mm-dd hh:mm:ss',
            ]
        ]);
    }

    /**
     * @param ActiveField $fieldWidget
     *
     * @return ActiveField
     */
    function fieldBoolean(Field $field, ActiveField $activeField)
    {
        return $activeField->checkbox();
    }
}