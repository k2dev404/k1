<?php

namespace K1\IO;

use K1\System\Config;
use K1\System\Exceptions\SystemException;

class File
{
    public static function read(string $path)
    {
        if (is_readable($path)) {
            return file_get_contents($path);
        }

        return null;
    }

    /**
     * @throws SystemException
     */
    public static function create(string $path, string $data = ''): void
    {
        $dir = dirname($path);

        Dir::create($dir);

        if (@file_put_contents($path, $data) === false) {
            throw new SystemException(sprintf('Невозможно создать файл "%s"', $path));
        }

        @chmod($path, Config::get('io')['file_chmod']);
    }

    /**
     * @throws SystemException
     */
    public static function copy(string $from, string $to): void
    {
        if (!is_file($from)) {
            throw new SystemException(sprintf('Не удалось найти файл "%s"', $from));
        }

        $dir = dirname($to);

        Dir::create($dir);

        if (@copy($from, $to) === false) {
            throw new SystemException(sprintf('Не удалось скопировать файл в "%s"', $to));
        }

        @chmod($to, Config::get('io')['file_chmod']);
    }

    public static function delete(string $path): void
    {
        unlink($path);
    }

    /**
     * @throws SystemException
     */
    public static function isPhoto(string $path): bool
    {
        if (!is_file($path)) {
            throw new SystemException(sprintf('Не удалось найти файл "%s"', $path));
        }

        if (!preg_match("#\.(jpe?g|gif|png)$#i", $path)) {
            return false;
        }

        $result = false;

        try {
            $size = @getimagesize($path);
            if (!in_array($size[2], [1, 2, 3])) {
                return false;
            }

            $list = ['', 'imagecreatefromgif', 'imagecreatefromjpeg', 'imagecreatefrompng'];

            $function = $list[$size[2]];

            if (@$function($path)) {
                $result = true;
            }
        } catch (\Exception $e) {

        }

        return $result;
    }
}