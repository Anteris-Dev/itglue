<?php

namespace Anteris\ITGlue\Support;

class Str
{
    protected static $studlyCache = [];
    protected static $camelCache  = [];

    public static function camel(string $string)
    {
        if (isset(static::$camelCache[$string])) {
            return static::$camelCache[$string];
        }

        return static::$camelCache[$string] = lcfirst(static::studly($string));
    }

    public static function studly(string $string)
    {
        if (isset(static::$studlyCache[$string])) {
            return static::$studlyCache[$string];
        }

        $value = ucwords(str_replace(['-', '_'], ' ', $string));

        return static::$studlyCache[$string] = str_replace(' ', '', $value);
    }
}
