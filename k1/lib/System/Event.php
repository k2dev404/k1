<?php

namespace K1\System;

class Event
{
    private static array $handlers = [];

    public static function on($type, $function)
    {
        if (is_array($type)) {
            foreach ($type as $item) {
                self::$handlers[$item][] = $function;
            }

            return;
        }

        self::$handlers[$type][] = $function;
    }

    public static function off($type)
    {
        if (!empty(self::$handlers[$type])) {
            unset(self::$handlers[$type]);
        }
    }

    public static function trigger($type, &$options = [])
    {
        if (!empty(self::$handlers[$type])) {
            foreach (self::$handlers[$type] as $item) {
                $params = [&$options];
                foreach (func_get_args() as $i => $arg) {
                    if ($i > 1) {
                        $params[] = $arg;
                    }
                }

                if ($result = call_user_func_array($item, $params)) {
                    return $result;
                }
            }
        }
    }
}