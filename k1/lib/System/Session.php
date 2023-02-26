<?php

namespace K1\System;

use K1\System\Exceptions\SessionException;

class Session
{
    /**
     * @throws SessionException
     */
    public function start(array $options = []): bool
    {
        $this->checkIfHeaderSent();

        return session_start($options);
    }

    public function all(): array
    {
        return $_SESSION ?? [];
    }

    public function has(string $name): bool
    {
        return isset($_SESSION[$name]);
    }

    public function get(string $name)
    {
        return $_SESSION[$name] ?? null;
    }

    /**
     * @throws SessionException
     */
    public function set(string $name, $value)
    {
        $this->checkIfSessionWasNotStarted();

        $_SESSION[$name] = $value;
    }

    /**
     * @throws SessionException
     */
    public function delete(string $name): void
    {
        $this->checkIfSessionWasNotStarted();

        unset($_SESSION[$name]);
    }

    /**
     * @throws SessionException
     */
    public function clear(): void
    {
        $this->checkIfSessionWasNotStarted();

        session_unset();
    }

    public function isStarted(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    /**
     * @throws SessionException
     */
    private function checkIfHeaderSent(): void
    {
        if (headers_sent($file, $line)) {
            throw new SessionException('Заголовки уже отправлены');
        }
    }

    /**
     * @throws SessionException
     */
    private function checkIfSessionWasNotStarted(): void
    {
        if (!$this->isStarted()) {
            throw new SessionException('Сессия не запущена');
        }
    }
}