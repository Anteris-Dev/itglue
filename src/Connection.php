<?php

namespace Anteris\ITGlue;

use Http\Client\Common\HttpMethodsClientInterface;

/**
 * This class is our connection manager. Every connection instance gets stored
 * and retrieved here. This allows our models to not worry about needing the client
 * in their constructor.
 */
class Connection
{
    /** @var HttpMethodsClientInterface[] An array of http clients. */
    private static array $instances = [];

    /**
     * Sorry, this class is a singleton!
     */
    private function __construct()
    {
    }

    /**
     * Returns the requested connection (if it exists).
     */
    public static function get(string $name = 'default'): HttpMethodsClientInterface
    {
        if (! static::has($name)) {
            //
        }

        return static::$instances[$name];
    }

    /**
     * Determines whether or not the specified connection exists.
     */
    public static function has(string $name): bool
    {
        return array_key_exists($name, static::$instances);
    }

    /**
     * Sets the specified connection.
     */
    public static function set(string $name, HttpMethodsClientInterface $client): void
    {
        static::$instances[$name] = $client;
    }
}
