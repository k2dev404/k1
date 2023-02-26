<?php

namespace K1\System\Exceptions;

class SystemException extends \Exception
{
    public function __construct(string $message = 'Неизвестная ошибка', $code = 0)
    {
        parent::__construct($message, $code);
    }
}