<?php

namespace Anteris\ITGlue\Support;

use Illuminate\Support\Arr as IlluminateArray;
use Illuminate\Support\Str;

class Arr extends IlluminateArray
{
    /**
     * Converts the keys in an array to camel case.
     *
     * @param  array  $array  The array to be transformed.
     * @return array
     */
    public static function camelKeys(array $array): array
    {
        $newArray = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = static::camelKeys($value);
            }

            if (is_int($key)) {
                $newArray[$key] = $value;

                continue;
            }

            $newArray[Str::camel($key)] = $value;
        }

        return $newArray;
    }

    /**
     * Converts the keys in an array to kebab case.
     *
     * @param  array  $array  The array to be transformed.
     * @return array
     */
    public static function kebabKeys(array $array): array
    {
        $newArray = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = static::kebabKeys($value);
            }

            if (is_int($key)) {
                $newArray[$key] = $value;

                continue;
            }

            $key            = Str::kebab(Str::camel($key));
            $newArray[$key] = $value;
        }

        return $newArray;
    }

    /**
     * Converts the keys in an array to snake case.
     *
     * @param  array  $array  The array to be transformed.
     * @return array
     */
    public static function snakeKeys(array $array): array
    {
        $newArray = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = static::snakeKeys($value);
            }

            if (is_int($key)) {
                $newArray[$key] = $value;

                continue;
            }

            $key            = Str::snake(Str::camel($key));
            $newArray[$key] = $value;
        }

        return $newArray;
    }
}
