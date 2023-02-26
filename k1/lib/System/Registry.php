<?php

namespace K1\System;

class Registry
{
    private static array $data = [];

    public static function set(string $key, $value): void
    {
        self::$data[$key] = $value;
    }

    public static function get(string $key)
    {
        if (array_key_exists($key, self::$data)) {
            return self::$data[$key];
        }

        return null;
    }

    public static function delete(string $key): void
    {
        if (array_key_exists($key, self::$data)) {
            unset(self::$data[$key]);
        }
    }
}