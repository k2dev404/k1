<?php

namespace K1\System;

use K1\Router\Router;
use K1\System\Exceptions\SystemException;
use K1\Web\Css;
use K1\Web\Js;

class Application
{
    private static ?Application $instance = null;
    private array $prop = [];
    private static $documentRoot = null;
    private string $baseDir;
    public string $content = '';

    private function __construct()
    {
        $this->baseDir = self::getDocumentRoot() . '/k1/components/';
    }

    public static function getInstance(): ?Application
    {
        if (self::$instance === null) {
            self::$instance = new Application();
        }

        return self::$instance;
    }

    public static function getDocumentRoot()
    {
        if (self::$documentRoot !== null) {
            return self::$documentRoot;
        }

        self::$documentRoot = str_replace('\\', '/', rtrim(dirname(__DIR__, 3), '/\\'));

        return self::$documentRoot;
    }

    /**
     * @throws SystemException
     */
    public function show404()
    {
        $router = Router::getInstance();
        $router->clearHandler();
        $this->run();

        exit;
    }

    public function setDesign($template)
    {
        $this->setProp('design', $template);
    }

    public function getDesign()
    {
        return $this->getProp('design');
    }

    public function setProp($key, $value)
    {
        $this->prop[$key] = $value;
    }

    public function getProp($key)
    {
        if (array_key_exists($key, $this->prop)) {
            return $this->prop[$key];
        }

        return null;
    }

    public function component($dir, $result = null, bool $return = false)
    {
        $path = trim($this->baseDir . $dir, '/') . '/';

        $ob = new Component($path);

        if ($return) {
            ob_clean();
            $ob->controller($result);
            $html = ob_get_contents();
            ob_end_clean();
            return $html;
        } else {
            $ob->controller($result);
        }
    }

    /**
     * @throws SystemException
     */
    public function run()
    {
        $router = Router::getInstance();
        $this->content = $router->run();

        if ($design = $this->getDesign()) {
            $path = $this->baseDir . $design . '.php';

            if (!file_exists($path)) {
                throw new SystemException(sprintf('Шаблон дизайна не найден %s', $path));
            }

            include $path;
        } else {
            echo $this->content;
        }
    }

    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @throws SystemException
     */
    public function addCss(string $path)
    {
        $ob = Css::getInstance();
        $ob->add($path);
    }

    public function showCss(string $path = '')
    {
        $ob = Css::getInstance();
        echo $ob->get($path);
    }

    /**
     * @throws SystemException
     */
    public function addJs(string $path)
    {
        $ob = Js::getInstance();
        $ob->add($path);
    }

    public function showJs(string $path = '')
    {
        $ob = Js::getInstance();
        echo $ob->get($path);
    }

    public function include(string $file)
    {
        $back = debug_backtrace();

        if (isset($back[0]['file'])) {
            $full = dirname($back[0]['file']) . '/' . $file . '.php';

            if (file_exists($full)) {
                include $full;
            }
        }
    }
}