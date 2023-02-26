<?php

namespace K1\DB;

use K1\System\Application;

class Logger
{
    private string $file;

    public function __construct()
    {
        $this->file = Application::getDocumentRoot() . '/k1/log/mysql.sql';
    }

    private function getFormat($options): string
    {
        $result = [];
        $result[] = 'time: ' . $options['time'];
        $result[] = '';
        $result[] = $options['query'];
        $result[] = '';

        foreach ($options['backtrace'] as $trace) {
            if (empty($trace['file'])) {
                continue;
            }

            if (!empty($trace['class']) && !empty($trace['type'])) {
                $name = $trace['class'] . $trace['type'] . $trace['function'] . '()';
            } else {
                $name = $trace['function'] . '()';
            }

            $result[] = $name . ' > ' . $trace['file'] . ':' . $trace['line'];
        }

        $result[] = '';
        $result[] = '---';
        $result[] = '';

        return implode(PHP_EOL, $result);
    }

    public function add($options)
    {
        $this->write($this->getFormat($options));
    }

    private function write($content)
    {
        file_put_contents($this->file, $content, FILE_APPEND);
    }
}