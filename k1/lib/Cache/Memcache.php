<?php

namespace K1\Cache;

use K1\System\Config;
use K1\System\Exceptions\SystemException;

class Memcache
{
    private static ?Memcache $instance = null;

    private \Memcache $memcache;
    private int $time;
    private string $key;

    /**
     * @throws SystemException
     */
    private function __construct()
    {
        $this->connect();
    }

    public static function getInstance(): ?Memcache
    {
        if (self::$instance === null) {
            self::$instance = new Memcache();
        }

        return self::$instance;
    }

    /**
     * @throws SystemException
     */
    private function connect()
    {
        $host = 'localhost';
        $port = 11211;

        $config = Config::get('memcache');

        if (!empty($config['host'])) {
            $host = $config['host'];
        }

        if (!empty($config['port'])) {
            $port = $config['port'];
        }

        $memcache = new \Memcache;
        $memcache->connect($host, $port);

        $this->memcache = $memcache;
    }

    public function get(string $key, int $time = 0, string $path = '')
    {
        $this->key = md5($key . $path);
        $this->time = $time;

        if ($this->time > 0) {
            $result = $this->memcache->get($this->key);

            if ($result && ($result = unserialize($result))) {
                if ($result['expire'] > time()) {
                    return $result['content'];
                } else {
                    $this->delete($key, $path);

                    return false;
                }
            }

            if ($result = $this->memcache->get($this->key)) {
                return $result;
            }
        }

        return false;
    }

    public function save($value): bool
    {
        if (strlen($this->key) && $this->time > 0) {
            $result = [
                'create' => time(),
                'expire' => (time() + $this->time),
                'content' => $value,
            ];

            return $this->memcache->set($this->key, serialize($result), false, $this->time);
        }

        return false;
    }

    public function delete(string $key, string $path = '')
    {
        $this->memcache->delete(md5($key . $path));
    }

    public function clear()
    {
        $this->memcache->flush();
    }
}