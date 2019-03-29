<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace svsoft\yii\items\widgets;

use svsoft\yii\items\services\ImageThumb;
use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\InputWidget;

class FileUploadWidget extends InputWidget
{
    public $multiple = false;

    public $files = [];

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        FileUploadAsset::register($this->view);

        parent::init();
    }

    public function run()
    {
        return $this->renderInput();
    }

    protected function renderInput()
    {
        /** @var ImageThumb $imageThumb */
        $imageThumb = \Yii::$container->get(ImageThumb::class);

        $multiple = $this->multiple;

        $inputName = Html::getInputName($this->model, $this->attribute) . ($multiple ? '[]' : '');

        $html = Html::input('file', $inputName, null, ['multiple' => $multiple]);

        $attributeValue = $this->model->{$this->attribute};

        $values = is_array($attributeValue) ? $attributeValue : ($attributeValue ? [$attributeValue] : null);


        if ($values)
        {
            $html .= Html::beginTag('div',['class'=>'row']);
            foreach($values as  $key=>$value)
            {
                $html .= Html::beginTag('div',['class'=>'file-upload-widget-img-item col-lg-2 col-sm-3 col-xs-6']);

                if ($filePath = ArrayHelper::getValue($this->files, $key) )
                {
                    if (!file_exists($filePath) || getimagesize($filePath) === false)
                    {
                        $html .= Html::tag( 'div', pathinfo($filePath, PATHINFO_BASENAME), ['class'=>'filename']);
                    }
                    else
                    {
                        $html .= Html::img( $imageThumb->thumbByParams($filePath, 200, 200) );
                    }
                }



                $html .= Html::activeHiddenInput($this->model, $this->attribute . ($multiple?"[$key]":''), ['value'=>$value]);
                $html .= Html::tag('div',
                    Html::label(
                        Html::checkbox(Html::getInputName($this->model, $this->attribute . ($multiple?"[$key]":'')), false, ['value'=>''])
                        .'Удалить'
                    )
                    ,['class'=>'checkbox']
                );

                $html .= Html::endTag('div');
            }
            $html .= Html::endTag('div');
        }

        return $html;
    }
}
