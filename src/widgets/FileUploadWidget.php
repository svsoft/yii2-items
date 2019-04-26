<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace svsoft\yii\items\widgets;

use svsoft\thumbnails\handlers\ResizeHandler;
use svsoft\thumbnails\Thumb;
use svsoft\thumbnails\ThumbnailsInterface;
use svsoft\yii\items\traits\GetThumbnailsTrait;
use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;
use yii\jui\Sortable;
use yii\widgets\InputWidget;

class FileUploadWidget extends InputWidget
{
    use GetThumbnailsTrait;

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
        /** @var ThumbnailsInterface $thumbnails */
        $thumb = new Thumb([new ResizeHandler(200,200)]);
        $thumbnails = $this::getThumbnails();

        $multiple = $this->multiple;

        $inputName = Html::getInputName($this->model, $this->attribute) . ($multiple ? '[]' : '');

        $html = Html::input('file', $inputName, null, ['multiple' => $multiple]);

        $attributeValue = $this->model->{$this->attribute};

        $values = is_array($attributeValue) ? $attributeValue : ($attributeValue ? [$attributeValue] : null);

        if ($values)
        {
            $htmlImageItems = [];
            foreach($values as  $key=>$value)
            {
                $htmlImageItem = '';
                if (is_string($value) && $filePath = ArrayHelper::getValue($this->files, $value) )
                {
                    if (!file_exists($filePath) || getimagesize($filePath) === false)
                    {
                        $htmlImageItem .= Html::tag( 'div', pathinfo($filePath, PATHINFO_BASENAME), ['class'=>'filename']);
                    }
                    else
                    {
                        $htmlImageItem .= Html::img( $thumbnails->getCreator()->create($filePath, $thumb) );
                    }
                }

                $htmlImageItem .= Html::activeHiddenInput($this->model, $this->attribute . ($multiple?"[$key]":''), ['value'=>$value]);
                $htmlImageItem .= Html::tag('div',
                    Html::label(
                        Html::checkbox(Html::getInputName($this->model, $this->attribute . ($multiple?"[$key]":'')), false, ['value'=>''])
                        .'Удалить'
                    )
                    ,['class'=>'checkbox']
                );

                $htmlImageItems[] = $htmlImageItem;
            }

            $html .= Sortable::widget([
                'items' => $htmlImageItems,
                'options' => ['tag' => 'div', 'class'=>'file-items row'],
                'itemOptions' => ['tag' => 'div', 'class'=>'file-upload-widget-img-item col-lg-2 col-md-3 col-sm-4 col-xs-6 file-item'],
                'clientOptions' => ['cursor' => 'move'],
            ]);
        }

        return $html;
    }
}
