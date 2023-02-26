<?php

namespace K1\Cache;

use K1\IO\Dir;
use K1\System\Application;
use K1\System\Exceptions\SystemException;

class File
{
    private static ?File $instance = null;

    private string $path;
    private int $time;
    private string $key;

    private function __construct()
    {

    }

    public static function getInstance(): ?File
    {
        if (self::$instance === null) {
            self::$instance = new File();
        }

        return self::$instance;
    }

    private function getPath(string $key, string $path = ''): string
    {
        $result = Application::getDocumentRoot() . '/k1/cache/';

        if ($path) {
            $result .= trim($path, '/\\') . '/';
        }

        $md5 = md5($key);

        $result .= substr($md5, 0, 3) . '/' . $md5 . '.php';

        return $result;
    }

    public function get(string $key, int $time = 0, string $path = '')
    {
        $this->path = $this->getPath($key, $path);
        $this->time = $time;
        $this->key = md5($key . $path);

        if ($time > 0 && file_exists($this->path)) {
            $expire = 0;
            $content = '';

            if (@include $this->path) {
                if ($expire > time()) {
                    return unserialize($content);
                }
            }
        }

        return false;
    }

    /**
     * @throws SystemException
     */
    public function save($value): bool
    {
        if (strlen($this->key) && $this->time > 0) {
            $result[] = '<?php';
            $result[] = '$create = ' . time() . ';';
            $result[] = '$expire = ' . (time() + $this->time) . ';';
            $result[] = '$content = \'' . serialize($value) . '\';';
            $result[] = '';
            $result[] = 'return true;';

            $content = implode("\r\n", $result);

            \K1\IO\File::create($this->path, $content);

            return true;
        }

        return false;
    }

    public function delete(string $key, string $path = '')
    {
        $full = $this->getPath($key, $path);

        if (file_exists($full)) {
            @unlink($full);
            @rmdir(dirname($full));
        }
    }

    /**
     * @throws SystemException
     */
    public function clear(string $path = '', bool $all = true)
    {
        $full = Application::getDocumentRoot() . '/k1/cache/';

        if ($path) {
            $full .= trim($path, '/\\') . '/';
        }

        if ($all) {
            Dir::clear($full);
        } else {
            $time = time();

            $list = Dir::getList($full);
            foreach ($list as $file) {
                if (substr($file, -3, 3) == 'php') {
                    $expire = '';

                    @include $file;

                    if ($time > $expire) {
                        @unlink($file);
                        @rmdir(dirname($file));
                    }
                }
            }
        }
    }
}