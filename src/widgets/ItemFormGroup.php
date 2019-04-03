<?php
namespace svsoft\yii\items\widgets;

use yii\base\BaseObject;

class ItemFormGroup extends BaseObject
{
    public $id;

    public $title;

    public $fields = [];

    public $submit = false;

}