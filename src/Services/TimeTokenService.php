<?php


namespace FreedomSex\Services;


use Psr\Cache\CacheItemPoolInterface;

class TimeTokenService
{
    const DEFAULT_DELAY = 2;
    const TOKEN_EXPIRE = 10;
    const TIMER_PREFIX = 'timer_start_identifier_';

    public function __construct(CacheItemPoolInterface $memory, $delay = null, $expire = null)
    {
        $this->cache = $memory;
        $this->defaultDelay = $delay;
        $this->defaultExpire = $expire;
    }

    public function setExpire($time)
    {
        $this->expire = $time;
        return $this;
    }

    public function setDelay($time)
    {
        $this->delay = $time;
        return $this;
    }

    public function expires($expect, $expire = null)
    {
        return $expect + ($expire ?? $this->defaultExpire ?? self::TOKEN_EXPIRE);
    }

    public function delay($time = null)
    {
        return $time ?? $this->defaultDelay ?? self::DEFAULT_DELAY;
    }

    public function save($id, $expect, $expire)
    {
        $cacheItem = $this->cache->getItem(self::TIMER_PREFIX . $id);
        $cacheItem->set($expect);
        $cacheItem->expiresAfter($this->expires($expect, $expire));
        $this->cache->save($cacheItem);
    }

    public function expect($id)
    {
        return $this->cache->getItem(self::TIMER_PREFIX . $id)->get();
    }

    public function start($id, $delay = null, $expire = null)
    {
        $delay = $this->delay($delay);
        $expect = time() + $delay;
        $this->save($id, $expect, $expire);
        return $delay;
    }

    public function bump($id, $delay = null, $expire = null)
    {
        return $this->start($id, $delay, $expire);
    }

    public function restore($id, $delay = null)
    {
        $delay = $this->delay($delay);
        $expect = $this->expect($id);
        if ($expect) {
            $delay = $expect - time();
        }
        return $delay;
    }

    public function left($id)
    {
        $expect = $this->expect($id);
        if (!$expect) {
            return null;
        }
        return time() - $expect;
    }

    public function ready($id)
    {
        $left = $this->left($id);
        if (is_null($left)) {
            return null;
        }
        return $left >= 0;
    }

}
