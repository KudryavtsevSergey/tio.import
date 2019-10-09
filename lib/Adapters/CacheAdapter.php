<?php

namespace Tio\Import\Adapters;

use Bitrix\Main\Data\Cache;

class CacheAdapter
{
    /**
     * @var Cache
     */
    protected $_cache = null;

    /**
     * @var string
     */
    protected $_id = null;

    /**
     * @var string
     */
    protected $_dir = null;

    /**
     * @var int
     */
    protected $_time = null;

    /**
     * @param string $id
     * @param string $dir
     * @param int $time в секундах
     */
    public function __construct(string $id, string $dir = "/travelsoft", int $time = 3600)
    {
        $this->_cache = Cache::createInstance();
        $this->_id = $id;
        $this->_dir = $dir;
        $this->_time = $time;
    }

    /**
     * Получение из кеша
     * @return array
     */
    public function get()
    {
        $result = array();
        if ($this->_cache->initCache($this->_time, $this->_id, $this->_dir)) {
            $result = $this->_cache->getVars();
        }
        return $result;
    }

    /**
     * Кеширование информации
     * @param callable $callback
     * @return array
     */
    public function caching(callable $callback)
    {
        $result = array();
        if ($this->_cache->startDataCache()) {
            $result = (array)$callback();
            if (is_array($result) && !empty($result)) {
                $this->_cache->endDataCache($result);
            } else {
                $this->_cache->abortDataCache();
            }
        }
        return $result;
    }

    /**
     * Устанавливает тегированный кеш
     * @param string $tag_cache_id
     */
    public function setTagCache(string $tag_cache_id)
    {
        if (defined('BX_COMP_MANAGED_CACHE')) {
            global $CACHE_MANAGER;
            $CACHE_MANAGER->StartTagCache($this->getCacheDir());
            $CACHE_MANAGER->RegisterTag($tag_cache_id);
            $CACHE_MANAGER->EndTagCache();
        }
    }

    /**
     * Очистка тегированного кеша по названию тега
     * @param string $tagName
     */
    public static function clearByTag(string $tagName)
    {
        global $CACHE_MANAGER;
        $CACHE_MANAGER->ClearByTag($tagName);
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return $this->_dir;
    }
}