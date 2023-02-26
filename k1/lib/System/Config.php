<?php

namespace K1\System;

use K1\System\Exceptions\SystemException;

class Config
{
    private static array $data = [];

    /**
     * @throws SystemException
     */
    public static function load(string $path)
    {
        if (!file_exists($path) || !is_file($path) || !is_readable($path)) {
            throw new SystemException(sprintf('Не удается загрузить конфигурационный файл "%s"', $path));
        }

        $config = include_once $path;

        self::$data = $config;
    }

    /**
     * @throws SystemException
     */
    public static function getAll(): array
    {
        if (empty(self::$data)) {
            throw new SystemException('Не загружен файл конфигурации');
        }

        return self::$data;
    }

    /**
     * @throws SystemException
     */
    public static function get(string $key)
    {
        if (array_key_exists($key, self::getAll())) {
            return self::$data[$key];
        }

        return [];
    }
}