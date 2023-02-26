<?php

namespace K1\System;

use K1\System\Exceptions\SystemException;

class ErrorHandler
{
    private array $config = [];

    /**
     * @throws SystemException
     */
    public function init()
    {
        $this->config = Config::get('error_handler');

        error_reporting($this->config['error_reporting']);
        ini_set('display_errors', true);

        set_error_handler([$this, 'handleError'], $this->config['error_reporting']);
        set_exception_handler([$this, 'handleException']);
    }

    /**
     * @throws SystemException
     */
    public function handleError($code, $message)
    {
        throw new SystemException($message, $code);
    }

    public function handleException($exception)
    {
        if (empty($this->config['debug']) && empty($this->config['log'])) {
            exit;
        }

        $result = [
            'title' => get_class($exception),
            'message' => $exception->getMessage()
        ];

        foreach ($exception->getTrace() as $i => $trace) {
            if (empty($trace['file'])) {
                continue;
            }

            $trace['number'] = $i + 1;

            if (!empty($trace['class']) && !empty($trace['type'])) {
                $trace['name'] = $trace['class'] . $trace['type'] . $trace['function'] . '()';
            } else {
                $trace['name'] = $trace['function'] . '()';
            }

            $file = $trace['file'];
            $line = $trace['line'];

            $lines = [];
            $begin = $end = 0;

            $half = 7;

            if ($line !== null) {
                $line--;
                $lines = @file($file);
                if ($line < 0 || $lines === false || ($lineCount = count($lines)) < $line) {
                    return '';
                }

                $begin = max($line - $half, 0);
                $end = $line + $half < $lineCount ? $line + $half : $lineCount - 1;
            }

            $trace['code'] = '';
            $trace['begin'] = $begin;
            $trace['end'] = $end;

            for ($i = $begin; $i <= $end; ++$i) {
                $trace['code'] .= (trim($lines[$i]) === '') ? " \n" : self::htmlEncode($lines[$i]);
            }

            $result['trace'][] = $trace;
        }

        http_response_code(500);
        ob_end_clean();

        if (!empty($this->config['log'])) {
            $log = new Logger();
            $log->add($result);
        }

        if (!empty($this->config['debug'])) {
            $app = Application::getInstance();
            $app->component('system/error', $result);
        }

        exit;
    }

    public static function htmlEncode($text): string
    {
        return htmlspecialchars($text, ENT_NOQUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
    }

    public static function setErrorHandler($text)
    {
        set_error_handler(static function (int $code, string $error) use ($text) {
            throw new SystemException($text . '. ' . $error, $code);
        });
    }

    public static function unsetErrorHandler()
    {
        restore_error_handler();
    }
}