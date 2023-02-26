<?php

namespace K1\System;

use K1\System\Exceptions\CookieException;
use K1\System\Exceptions\SystemException;

class Cookie
{
    private int $expires = 0;
    private bool $http_only = false;
    private string $domain;
    private string $path = '/';
    private bool $secure = false;
    private string $same_site = 'lax';
    private string $validation = '';
    private string $hash_method = 'sha256';

    /**
     * @throws CookieException
     * @throws SystemException
     */
    public function __construct($domain = '', $expires = 0, $path = '/', $secure = false, $same_site = 'lax')
    {
        if ($config = Config::get('cookie')) {
            $options = $config;
        } else {
            $options = [];
        }

        if ($arg = func_get_args()) {
            $keys = [
                'domain',
                'expires',
                'path',
                'secure',
                'same_site'
            ];

            foreach ($arg as $i => $value) {
                if (isset($keys[$i])) {
                    $options[$keys[$i]] = $value;
                }
            }
        }

        if (!empty($options['validation']) && !empty($options['validation_key'])) {
            $options['validation'] = $config['validation_key'];

            if (!empty($options['hash_method'])) {
                $this->setHashMethod($options['hash_method']);
            }
        }

        $this->setOptions($options);
    }

    /**
     * @throws CookieException
     */
    public function setOptions(array $options = []): void
    {
        if (isset($options['expires'])) {
            $this->setExpires($options['expires']);
        }

        if (isset($options['http_only'])) {
            $this->setHttpOnly($options['http_only']);
        }

        if (isset($options['domain'])) {
            $this->setDomain($options['domain']);
        }

        if (isset($options['path'])) {
            $this->setPath($options['path']);
        }

        if (isset($options['secure'])) {
            $this->setSecure($options['secure']);
        }

        if (isset($options['same_site'])) {
            $this->setSameSite($options['same_site']);
        }

        if (isset($options['validation'])) {
            $this->setValidation($options['validation']);
        }

        if (isset($options['hash_method'])) {
            $this->setHashMethod($options['hash_method']);
        }
    }

    public function setExpires(int $value): Cookie
    {
        $this->expires = $value;

        return $this;
    }

    public function getExpires(): int
    {
        return $this->expires;
    }

    public function setDomain($value): Cookie
    {
        $this->domain = $value;

        return $this;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function setPath($value): Cookie
    {
        $this->path = $value;

        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setHttpOnly($value): void
    {
        $this->domain = $value;
    }

    public function getHttpOnly(): bool
    {
        return $this->http_only;
    }

    public function setSecure($value): Cookie
    {
        $this->secure = $value;

        return $this;
    }

    public function getSecure(): bool
    {
        return $this->secure;
    }

    public function setValidation($key): Cookie
    {
        $this->validation = $key;

        return $this;
    }

    /**
     * @throws CookieException
     */
    public function setSameSite(string $value): Cookie
    {
        $values = ['none', 'strict', 'lax'];

        if ($value && !in_array(strtolower($value), $values)) {
            throw new CookieException('Неправильное значение параметра SameSite. Доступные значения: ' . implode(' | ', $values));
        }

        $this->same_site = $value;

        return $this;
    }

    public function getSameSite(): string
    {
        return $this->same_site;
    }

    public function getValidation(): string
    {
        return $this->validation;
    }

    public function setHashMethod($value): Cookie
    {
        $this->hash_method = $value;

        return $this;
    }

    public function getHashMethod(): string
    {
        return $this->hash_method;
    }

    /**
     * @throws CookieException
     */
    private function checkIfHeaderSent(): void
    {
        if (headers_sent($file, $line)) {
            throw new CookieException('Заголовки уже были отправлены');
        }
    }

    /**
     * @throws CookieException
     */
    private function getValidationHash($value, $validation): string
    {
        if (!in_array($this->getHashMethod(), hash_hmac_algos())) {
            throw new CookieException('Алгоритм шифрования не поддерживается системой ' . $this->getHashMethod());
        }

        return hash_hmac($this->getHashMethod(), $value, $validation);
    }

    private function getLength($value): int
    {
        return mb_strlen((string)$value, '8bit');
    }

    /**
     * @throws CookieException
     */
    private function getValidationDecodeValue($value, $validation): ?string
    {
        $hash = $this->getValidationHash('', '');
        $length = $this->getLength($hash);

        if ($this->getLength($hash) >= $length) {
            $cookie_hash = substr($value, 0, $length);
            $content = substr($value, $length);

            $actual_hash = $this->getValidationHash($content, $validation);

            if ($cookie_hash === $actual_hash) {
                return substr($value, $length);
            }
        }

        return null;
    }

    /**
     * @throws CookieException
     */
    public function set(string $name, $value, $expires = null): void
    {
        $this->checkIfHeaderSent();

        $options = [
            'expires' => ($expires === null ? $this->getExpires() : $expires),
            'path' => $this->getPath(),
            'domain' => $this->getDomain(),
            'secure' => $this->getSecure(),
            'httponly' => $this->getHttpOnly(),
            'samesite' => $this->getSameSite(),
        ];

        if ($validation = $this->getValidation()) {
            $value = $this->getValidationHash($value, $validation) . $value;
        }

        if (\setcookie($name, $value, $options)) {
            $_COOKIE[$name] = $value;
        }
    }

    /**
     * @throws CookieException
     */
    public function get(string $name)
    {
        if (!isset($_COOKIE[$name])) {
            return null;
        }

        if ($validation = $this->getValidation()) {
            return $this->getValidationDecodeValue($_COOKIE[$name], $validation);
        } else {
            return $_COOKIE[$name];
        }
    }

    public function has(string $name): bool
    {
        return isset($_COOKIE[$name]);
    }

    /**
     * @throws CookieException
     */
    public function delete(string $name): void
    {
        $this->set($name, '', -1);
    }
}