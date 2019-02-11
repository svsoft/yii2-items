<?php
namespace svsoft\yii\items\services;

use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use Imagine\Image\ImageInterface;

class ImageThumb extends Component
{
    const MODE_INSET = ImageInterface::THUMBNAIL_INSET;
    const MODE_OUTBOUND  = ImageInterface::THUMBNAIL_OUTBOUND;
    const MODE_FIXED = 'fixed';

    /**
     * Массив настроек превьюшек
     *
     * @var
     */
    public $thumbs = [];

    public $blankFilePath;

    public $defaultThumb = [
        'width'=>600,
        'height'=>600,
        'mode'=> self::MODE_INSET,
    ];

    public $thumbDirPath = '@app/web/resize';

    public $thumbWebDirPath = '@web/resize';

    public function init()
    {
        parent::init();

        $this->validateThumbs();

        $this->thumbDirPath = Yii::getAlias($this->thumbDirPath);

        $this->thumbWebDirPath = Yii::getAlias($this->thumbWebDirPath);

        if ($this->blankFilePath)
            $this->blankFilePath = Yii::getAlias($this->blankFilePath);
    }

    protected function validateThumbs()
    {
        foreach($this->thumbs as $name=>&$thumb)
        {
            if (empty($thumb['width']))
                throw new InvalidConfigException("param thumbs[{$name}]['width'] is not set");

            if (empty($thumb['height']))
                throw new InvalidConfigException("param thumbs[{$name}]['height'] is not set");
        }
    }

    protected function createDir()
    {
        return FileHelper::createDirectory($this->thumbDirPath);
    }

    public function getPath($filename)
    {
        return $this->thumbDirPath . DIRECTORY_SEPARATOR . $filename;
    }

    public function getWebPath($filename)
    {
        return $this->thumbWebDirPath . DIRECTORY_SEPARATOR . $filename;
    }

    public function generateFilename($filePath, $width, $height, $mode)
    {
        $info = pathinfo($filePath);

        return "{$info['filename']}-{$width}x{$height}-{$mode}.{$info['extension']}";
    }

    /**
     * @param $filePath
     * @param $width
     * @param $height
     * @param string $mode
     *
     * @return string
     * @throws Exception
     */
    public function createThumb($filePath, $width, $height, $mode = self::MODE_INSET)
    {
        if (!$filePath)
            throw new Exception('Param $filePath is not set');

        if (!is_file($filePath))
            throw new Exception('"' . $filePath . '" is not a file');

        $filename = $this->generateFilename($filePath, $width, $height, $mode);
        $thumbFilePath = $this->getPath($filename);
        if (file_exists($thumbFilePath))
            return $thumbFilePath;

        $this->createDir();


        $imagine = new Imagine\Gd\Imagine();
        $size    = new Imagine\Image\Box($width, $height);

        $thumbnailMode = $mode;
        if ($mode == self::MODE_FIXED)
            $thumbnailMode = self::MODE_INSET;

        /** @var Imagine\Image\ImageInterface $thumb */
        $thumb = $imagine->open($filePath)->thumbnail($size, $thumbnailMode);

        if ($mode == self::MODE_FIXED)
        {
            $width = $thumb->getSize()->getWidth();
            $height = $thumb->getSize()->getHeight();
            $thumb = $imagine->create($size)->paste($thumb, new Imagine\Image\Point(($size->getWidth() - $width)/2, ($size->getHeight() - $height)/2));
        }


        $thumb->save($thumbFilePath, array('jpeg_quality' => 90));

        return $thumbFilePath;
    }

    /**
     * Создает первью катринки и возвразает урл
     *
     * @param $filePath
     * @param $width
     * @param $height
     * @param string $mode
     * @param string $blankFilePath
     *
     * @return string
     */
    public function thumbByParams($filePath, $width, $height, $mode = self::MODE_INSET, $blankFilePath = '')
    {
        if (!$filePath || !file_exists($filePath) || is_dir($filePath))
        {
            if (!$blankFilePath)
                $blankFilePath = $this->blankFilePath;

            if ($blankFilePath)
                $filePath = $blankFilePath;
            else
                return false;
        }

        $thumbFilePath = $this->createThumb($filePath, $width, $height, $mode);

        if ($thumbFilePath)
        {
            $filename = pathinfo($thumbFilePath, PATHINFO_BASENAME);

            return $this->getWebPath($filename);
        }

        return '';
    }

    public function thumb($filePath, $thumbName)
    {
        if (!$thumb = ArrayHelper::getValue($this->thumbs, $thumbName))
            $thumb = $this->defaultThumb;

        if (empty($thumb['mode']))
            $thumb['mode'] = self::MODE_INSET;

        return $this->thumbByParams($filePath, $thumb['width'], $thumb['height'], $thumb['mode']);
    }
}