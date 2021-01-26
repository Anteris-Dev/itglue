<?php

namespace Anteris\ITGlue\Support;

use Psr\Http\Message\ResponseInterface;
use Ramsey\Collection\AbstractCollection;

class Response
{
    public static function toArray(ResponseInterface $response): array
    {
        $body = json_decode($response->getBody(), true);

        if (! isset($body['data']) || empty($body['data'])) {
            return [];
        }

        if (isset($body['data']['attributes'])) {
            return static::normalizeResource($body['data']);
        }

        $array = [];

        foreach ($body['data'] as $resource) {
            $array[] = static::normalizeResource($resource);
        }

        return $array;
    }

    public static function toCollection(ResponseInterface $response, AbstractCollection $collection): AbstractCollection
    {
        $data = static::toArray($response);

        foreach ($data as $resource) {
            $collection[] = new ($collection->getType())($resource);
        }

        return $collection;
    }

    protected static function normalizeResource(array $array)
    {
        $newArray = [];
        
        if (isset($array['id'])) {
            $newArray['id'] = $array['id'];
        }

        if (isset($array['attributes'])) {
            foreach ($array['attributes'] as $key => $value) {
                $newArray[Str::camel($key)] = $value;
            }
        }

        return $newArray;
    }
}
