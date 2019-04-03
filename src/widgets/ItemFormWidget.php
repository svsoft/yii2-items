<?php

namespace svsoft\yii\items\widgets;

use dosamigos\ckeditor\CKEditor;
use mihaildev\elfinder\ElFinder;
use svsoft\yii\items\entities\Field;
use svsoft\yii\items\entities\FileAttribute;
use svsoft\yii\items\forms\ItemForm;
use yii\base\InvalidCallException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;
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

    /**
     * @var ItemFormGroup[]|array
     */
    public $groups = [];

    /**
     * @var ItemFormBlock[]|array
     */
    public $blocks = [];

    public $defaultBlocks = [];

    public $defaultGroup = [];

    /**
     * @throws \yii\base\InvalidConfigException
     */
    function init()
    {
        $this->prepareGroups();

        $this->prepareBlocks();

        parent::init();
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    protected function prepareBlocks()
    {
        $this->defaultBlocks = [
            'top'=>[
                'cols'=>12,
            ],
            'left'=>[
                'cols'=>6,
            ],
            'right'=>[
                'cols'=>6,
            ],
            'bottom'=>[
                'cols'=>12,
            ],
        ];

        if (!$this->blocks)
        {
            $id = key($this->defaultBlocks);
            $block = current($this->defaultBlocks);
            $block['id'] = $id;
            $this->blocks[] = $block;
        }

        $blocks = [];
        $allGroups = array_keys($this->groups);
        foreach($this->blocks as $block)
        {
            if (isset($this->defaultBlocks[$block['id']]))
            {
                $block = ArrayHelper::merge($this->defaultBlocks[$block['id']], $block);
            }


            if (empty($block['class']))
            {
                $block['class'] = ItemFormBlock::class;
            }

            /** @var ItemFormBlock $block */
            $block = \Yii::createObject($block);

            $blocks[$block->id] = $block;


            $allGroups = array_diff($allGroups, $block->groups);
        }

        $this->blocks = $blocks;

        if ($allGroups)
        {
            foreach($this->blocks as $block)
            {
                if (!$block->groups)
                    $block->groups = $allGroups;
            }
        }
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    protected function prepareGroups()
    {
        if (!$this->itemForm)
            throw new InvalidCallException('Property itemForm must be set');

        $this->defaultGroup = [
            'title' => '',
            'id'    => 'default',
        ];

        if (!$this->groups)
            $this->groups[] = $this->defaultGroup;

        $groups = [];
        $allFields = $this->getAllFields();
        $submitFound = false;
        foreach($this->groups as $group)
        {
            if (empty($group['class']))
            {
                $group['class'] = ItemFormGroup::class;
            }
            /** @var ItemFormGroup $group */
            $group = \Yii::createObject($group);
            $groups[$group->id] = $group;

            $allFields = array_diff($allFields, $group->fields);

            if ($group->submit)
                $submitFound = true;
        }

        if (!$submitFound)
            end($groups)->submit = true;

        $this->groups = $groups;

        if ($allFields)
        {
            foreach($this->groups as $group)
            {
                if (!$group->fields)
                    $group->fields = $allFields;
            }
        }
    }

    function renderGroups()
    {
        $html = '';
        foreach($this->groups as $group)
            $html .= $this->renderGroup($group->id);

        return $html;
    }

    function renderGroup($id)
    {
        $group = $this->getGroup($id);

        $html = Html::beginTag('div', ['class'=>'box box-primary']);

        $html .= $this->renderGroupHeader($group->title);

        $html .= Html::beginTag('div', ['class'=>'box-body']);
        $actionFields = $this->fields($group->fields);

        foreach($actionFields as $actionField)
        {
            $html .= (string)$actionField;
        }

        if ($group->submit)
            $html .= Html::submitButton('Сохранить', ['class'=>'btn btn-success']);

        $html .= Html::endTag('div');

        $html .= Html::endTag('div');

        return $html;
    }

    function renderBlock($id)
    {
        $block = $this->getBlock($id);

        $html = Html::beginTag('div',['class'=>'col-lg-'.$block->cols]);

        foreach($block->groups as $groupId)
        {
            $html .= $this->renderGroup($groupId);
        }

        $html .= Html::endTag('div');

        return $html;
    }

    function renderBlocks()
    {
        $html = Html::beginTag('div',['class'=>'row']);

        foreach($this->blocks as $block)
            $html .= $this->renderBlock($block->id);

        $html .= Html::endTag('div');

        return $html;
    }

    /**
     * @param $id
     *
     * @return ItemFormBlock
     */
    protected function getBlock($id)
    {
        return $this->blocks[$id];
    }

    /**
     * @param $id
     *
     * @return ItemFormGroup
     */
    protected function getGroup($id)
    {

        return $this->groups[$id];
    }

    function beginGroup($id)
    {
        $group = $this->getGroup($id);

        return '<div class="item-index box box-primary">' . $this->renderGroupHeader($group->title);
    }

    function endGroup()
    {
        return '</div>';
    }

    function renderGroupHeader($title)
    {
        if (!$title)
            return '';

        return '<div class="box-header with-border"><h3 class="box-title">' . $title . '</h3></div>';
    }


    function beginBlock($id)
    {
        $block = $this->getBlock($id);

        return '<div class="col-lg-'.$block->cols.'">';
    }

    function endBlock()
    {
        return '</div>';
    }

    protected function getAllFields()
    {
        $fields = [];
        foreach($this->itemForm->itemType->getFields() as $field)
        {
            $fields[] = $field->getName();
        }

        return $fields;
    }


    /**
     * @param array $fields
     *
     * @return array
     * @throws \svsoft\yii\items\exceptions\FieldNotFoundException
     */
    function fields($fields = [])
    {
        if (empty($fields))
            $fields = $this->getAllFields();

        $fieldWidgets = [];
        foreach($fields as $fieldName)
        {
            $field = $this->itemForm->itemType->getFieldByName($fieldName);

            $fieldWidget = $this->field($this->itemForm, $fieldName);

            if (isset($this->labels[$fieldName]))
                $label = $this->labels[$fieldName];
            else
                $label = Inflector::camel2words($fieldName);

            if ($field->getType()->getRequired())
                $label = '*' . $label;

            $fieldWidget->label($label);

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