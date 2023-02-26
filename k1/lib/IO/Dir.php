<?php

namespace K1\IO;

use K1\System\Application;
use K1\System\Config;
use K1\System\Exceptions\SystemException;

class Dir
{
    /**
     * @throws SystemException
     */
    public static function create(string $path): bool
    {
        if (is_writable($path)) {
            return true;
        }

        mkdir($path, Config::get('io')['dir_chmod'], true);

        return true;
    }

    /**
     * @throws SystemException
     */
    public static function getList(string $path, string $view = 'absolute'): array
    {
        if (!is_dir($path)) {
            throw new SystemException(sprintf('Не удается найти папку "%s"', $path));
        }

        if ($view == 'root') {
            $short = strlen(Application::getDocumentRoot());
        } else if ($view == 'current')  {
            $short = strlen($path);
        } else {
            $short = 0;
        }

        $ob = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS),
            \RecursiveIteratorIterator::SELF_FIRST,
            \RecursiveIteratorIterator::CATCH_GET_CHILD
        );

        $result = [];
        foreach ($ob as $path => $file) {
            if ($file->isDir()) {
                $path .= '/';
            }

            if ($short) {
                $path = substr($path, $short);
            }

            $result[] = $path;
        }

        return $result;
    }

    /**
     * @throws SystemException
     */
    public static function copy(string $from, string $to): void
    {
        if (!is_readable($from)) {
            throw new SystemException(sprintf('Папка не существует или недоступна для чтения "%s"', $from));
        }

        $list = self::getList($from);

        if (!$list) {
            throw new SystemException('Папка пуста "' . $from . '"');
        }

        $chmod = [
            'dir' => Config::get('io')['dir_chmod'],
            'file' => Config::get('io')['file_chmod'],
        ];

        foreach ($list as $item) {
            if (substr($item, -1, 1) == '/') {
                if (!file_exists($to . $item)) {
                    mkdir($to . $item, $chmod['dir'], true);
                }
            } else {
                copy($from . $item, $to . $item);
                chmod($to . $item, $chmod['file']);
            }
        }
    }

    /**
     * @throws SystemException
     */
    public static function clear(string $path): void
    {
        if (!$path || ($path == '/') || !is_dir($path)) {
            throw new SystemException(sprintf('Некорректный путь "%s"', $path));
        }

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::CHILD_FIRST) as $file) {
            if (in_array($file->getFilename(), ['.', '..'])) {
                continue;
            }

            is_dir($file) ? rmdir($file) : unlink($file);
        }
    }

    /**
     * @throws SystemException
     */
    public static function unlink(string $path): void
    {
        if (!is_link($path)) {
            throw new SystemException(sprintf('Файл не является символической ссылкой "%s"', $path));
        }

        if (!is_writable($path)) {
            chmod($path, 0777);
        }

        unlink($path);
    }

    /**
     * @throws SystemException
     */
    public static function delete(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        self::clear($path);

        if (is_link($path)) {
            self::unlink($path);
        } else {
            rmdir($path);
        }
    }
}