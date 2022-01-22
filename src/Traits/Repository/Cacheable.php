<?php

namespace HZ\Illuminate\Mongez\Traits\Repository;

use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Cache\Store as CacheStorage;

trait Cacheable
{
    /**
     * Get record from redis cache
     * 
     * @param string $key
     * @return mixed  
     */
    public function getCache(string $key)
    {
        return $this->driver()->get($this->getCacheKey($key));
    }

    /**
     * Set record to redis cache
     * 
     * @param string $key
     * @param mixed $value
     * @return void  
     */
    public function setCache(string $key, $value)
    {
        return $this->driver()->put($this->getCacheKey($key), $value);
    }

    /**
     * Forget from cache by key  
     * 
     * @param string $key
     * @param mixed $value
     * @return void  
     */
    public function forgetCache(string $key)
    {
        $this->driver()->forget($this->getCacheKey($key));
    }

    /**
     * Get cache driver
     * 
     * @return string cache drive 
     * @return CacheStorage
     */
    protected function driver(): CacheStorage
    {
        return Cache::store(config('mongez.repository.cache.driver'));
    }

    /**
     * Get cache key
     * 
     * @param  string $key
     * @return string
     */
    protected function getCacheKey(string $key): string
    {
        return static::NAME . $key;
    }
}
