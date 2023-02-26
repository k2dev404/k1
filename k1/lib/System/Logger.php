<?php

namespace K1\System;

class Logger
{
    private string $file;

    public function __construct()
    {
        $this->file = Application::getDocumentRoot() . '/k1/log/system.txt';
    }

    private function getFormat($options): string
    {
        $result = [];
        $result[] = 'date: ' . date('Y-m-d H:i:s') . ' ' . $options['title'];
        $result[] = '';
        $result[] = str_replace('<hr>', PHP_EOL, $options['message']);
        $result[] = '';

        if (!empty($options['trace'])) {
            foreach ($options['trace'] as $trace) {
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
        }

        $result[] = '';
        $result[] = '---';
        $result[] = '';
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