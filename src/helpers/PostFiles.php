<?php
namespace svsoft\yii\items\helpers;

use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

class PostFiles
{
    /**
     * @param $data
     * @param null $formName
     *
     * @return array
     */
    static function getFiles($data, $formName = null)
    {
        if ($formName)
            $data = ArrayHelper::getValue($data, $formName, []);

        if (!$data)
            return [];

        $attributes = [];
        foreach(['name','type','tmp_name','error','size'] as $param)
        {
            foreach($data[$param] as $attribute=>$value)
            {
                if (is_array($value))
                {
                    $values = $value;
                    foreach($values as $key=>$value)
                        $attributes[$attribute][$key][$param] = $value;
                }
                else
                {
                    $attributes[$attribute][$param] = $value;
                }
            }
        }

        foreach($attributes as $name=>$file)
        {
            reset($file);
            if (is_numeric(key($file)))
            {
                $files = $file;
                foreach($files as $key=>$file)
                {
                    $attributes[$name][$key] = self::createUploadedFile($file);
                    if(!$file['name'])
                        unset($attributes[$name][$key]);
                }
            }
            else
            {
                $attributes[$name] = self::createUploadedFile($file);
                if(!$file['name'])
                    unset($attributes[$name]);

            }
        }


        return $attributes;
    }

    /**
     * @param $data
     *
     * @return UploadedFile
     */
    static function createUploadedFile($data)
    {
        $data['tempName'] = $data['tmp_name'];
        unset($data['tmp_name']);

        return new UploadedFile($data);
    }

}