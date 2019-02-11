<?php
namespace svsoft\yii\items\widgets;

use svsoft\yii\items\repositories\FileStorage;
use svsoft\yii\items\services\ImageThumb;
use yii\helpers\Html;

class ActiveField extends \yii\widgets\ActiveField
{

    public $model;

    public $attribute;

    public $field;
    /**
     * @var ItemFormWidget
     */
    public $form;

    function fileInput($options = [])
    {

        /** @var FileStorage $fileStorage */
        $fileStorage = \Yii::$container->get(FileStorage::class);
        /** @var \svsoft\yii\items\services\ImageThumb $imageThumb */
        $imageThumb = \Yii::$container->get(ImageThumb::class);


        $field = parent::fileInput($options);


        if (empty($options['multiple']))
            $options['multiple'] = false;

        $multiple = $options['multiple'];

        $inputName = Html::getInputName($this->model, $this->attribute);

        $html = Html::input('file', $inputName, null, $options);

        $attributeValue = $this->model->{$this->attribute};

        $values = is_array($attributeValue) ? $attributeValue : ($attributeValue ? [$attributeValue] : null);

        foreach($values as  $key=>$value)
        {

            $filePath = $fileStorage->getPath($value);
            $src = $imageThumb->thumbByParams($filePath, 200, 200);

            $html .= Html::beginTag('div',['class'=>'file-upload-widget-img-item col-lg-2 col-sm-3 col-xs-6']);
            $html .= Html::img( $src );
            $html .= Html::activeHiddenInput($this->model, $this->attribute . ($multiple?"[$key]":''), ['value'=>$value]);
            $html .= Html::tag('div',
                Html::label(
                    Html::checkbox(Html::getInputName($this->model, $this->attribute . ($multiple?"[$key]":'')), false, ['value'=>''])
                )
                ,['class'=>'checkbox']
            );

            $html .= Html::endTag('div');
        }

        $field->parts['{input}'] = $html;


        return $this;
    }
}