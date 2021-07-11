<?php
declare(strict_types=1);

namespace BrenoRoosevelt\RouteAttributeProvider\League\Tests;

use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

class Psr16ArrayCache implements CacheInterface
{
    protected $cached = [];

    public function get($key, $default = null)
    {
        if (!is_string($key)) {
            throw new class extends \Exception implements InvalidArgumentException {
            };
        }

        return $this->cached[$key] ?? null;
    }

    public function set($key, $value, $ttl = null)
    {
        $this->cached[$key] = $value;
    }

    public function delete($key)
    {
        unset($this->cached[$key]);
    }

    public function clear()
    {
        $this->cached = [];
    }

    public function getMultiple($keys, $default = null)
    {
        foreach ($keys as $key) {
            yield $this->get($key);
        }
    }

    public function setMultiple($values, $ttl = null)
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }
    }

    public function deleteMultiple($keys)
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }
    }

    public function has($key)
    {
        return array_key_exists($key, $this->cached);
    }

    public function isEmpty(): bool
    {
        return empty($this->cached);
    }
}
