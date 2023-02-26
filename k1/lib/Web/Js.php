<?php

namespace K1\Web;

use K1\IO\File;
use K1\System\Application;
use K1\System\Exceptions\SystemException;

class Js
{
    private static ?Js $instance = null;
    private array $data = [];

    private function __construct()
    {

    }

    public static function getInstance(): ?Js
    {
        if (self::$instance === null) {
            self::$instance = new Js();
        }

        return self::$instance;
    }

    /**
     * @throws SystemException
     */
    public function add(string $path)
    {
        $full = Application::getDocumentRoot() . $path;

        if (!file_exists($full)) {
            throw new SystemException(sprintf('Файл скриптов не найден %s', $full));
        }

        $this->data['file'][] = $path;
    }

    public function get(string $path = ''): string
    {
        $result = '';

        if (!array_key_exists('file', $this->data)) {
            return '';
        }

        if ($path) {
            return '<script src="' . $this->glue($path) . '"></script>' . PHP_EOL;
        }

        foreach ($this->data['file'] as $item) {
            $result .= '<script src="' . $item . '"></script>' . PHP_EOL;
        }

        return $result;
    }

    /**
     * @throws SystemException
     */
    private function glue(string $path): string
    {
        $root = Application::getDocumentRoot();

        $hex = '';
        foreach ($this->data['file'] as $item) {
            $hex .= filemtime($root . $item);
            $hex .= $item;
        }

        $md5 = md5($hex);

        $replace = true;
        if (file_exists($root . $path)) {
            $fp = @fopen($root . $path, "r");
            if ($fp) {
                if (($buffer = fgets($fp, 50)) !== false) {
                    if (preg_match("#/\* (.+) \*/#", $buffer, $match)) {
                        if ($match[1] == $md5) {
                            $replace = false;
                        }
                    }
                }
                fclose($fp);
            }
        }

        if ($replace) {
            $result = [];
            $result[] = '/* ' . $md5 . ' */';
            foreach ($this->data['file'] as $item) {
                $result[] = '/* ' . $item . ' */';
                $result[] = '';

                $content = file_get_contents($root . $item);

                $result[] = $content;
                $result[] = '';
            }

            File::create($root . $path, implode(PHP_EOL, $result));
        }

        return $path . '?' . $md5;
    }
}