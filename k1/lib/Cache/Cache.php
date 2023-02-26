<?php

namespace K1\Cache;

use K1\System\Config;
use K1\System\Exceptions\SystemException;

class Cache
{
    private $provider;

    /**
     * @throws SystemException
     */
    private function getProvider()
    {
        $config = Config::get('cache');

        if (!empty($config['type']) && $config['type'] == 'memcache') {
            return Memcache::getInstance();
        } else {
            return File::getInstance();
        }
    }

    /**
     * @throws SystemException
     */
    public function __construct()
    {
        $this->provider = $this->getProvider();
    }

    public function get(string $key, int $time = 0, string $path = '')
    {
        return $this->provider->get($key, $time, $path);
    }

    /**
     * @throws SystemException
     */
    public function save($value): ?bool
    {
        return $this->provider->save($value);
    }

    public function delete(string $key, string $path = '')
    {
        $this->provider->delete($key, $path);
    }

    /**
     * @throws SystemException
     */
    public function clear(string $path = '', bool $all = true)
    {
        $this->provider->clear($path, $all);
    }
}