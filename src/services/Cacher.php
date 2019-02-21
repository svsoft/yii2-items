<?php
namespace svsoft\yii\items\services;

use Yii;
use yii\base\Component;
use yii\caching\TagDependency;

/**
 * Осуществляет чистку кеша
 * Первая версия чистки кеша сделана только по типа
 * т.е. Если изменится тип, удаляются весть кеш по типу
 * Если изменится элемент, удаляются весть кеш по типу этого элемента
 * Если изменится свойство, также удаляются весть кеш по типу свойства
 * Очистка кеша происходит рекурсивно
 *
 * Class Cacher
 * @package svsoft\yii\content\components
 *
 */
class Cacher extends Component
{

    const TAG_ITEM_TYPE = 'items-item-type';

    /**
     * @var \yii\caching\CacheInterface
     */
    protected $cache;

    function __construct($config = [])
    {
        $this->cache = Yii::$app->cache;

        parent::__construct($config);
    }

    /**
     * @param $itemTypeId
     *
     * @return string
     */
    protected function getItemTypeTag($itemTypeId)
    {
        return self::TAG_ITEM_TYPE . '-' . $itemTypeId;
    }

    public function cleanByItemType($itemTypeId)
    {
        $tag = $this->getItemTypeTag($itemTypeId);

        TagDependency::invalidate($this->cache, $tag);
    }

    /**
     * @param $cacheKey
     * @param $value
     * @param $itemTypeId
     *
     * @return mixed
     */
    public function set($cacheKey, $value, $itemTypeId)
    {
        if (!is_array($itemTypeId))
            $itemTypeId = [$itemTypeId];

        $tags = [];
        foreach($itemTypeId as $id)
            $tags[] = $this->getItemTypeTag($id);

        $dependency = new TagDependency(['tags' => $tags]);

        return $this->cache->set($cacheKey, $value, null, $dependency);
    }

    public function get($cacheKey)
    {
        return $this->cache->get($cacheKey);
    }
}
