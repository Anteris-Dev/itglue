<?php

namespace Anteris\ITGlue\Orm;

use Anteris\ITGlue\Parser\Parser;
use Anteris\ITGlue\Support\Exception\InvalidLimitException;
use Anteris\ITGlue\Support\Exception\InvalidModelException;
use Anteris\ITGlue\Support\Exception\InvalidSortException;
use Http\Client\Common\HttpMethodsClientInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Throwable;

/**
 * @mixin \Anteris\ITGlue\Orm\Builder
 */
class Builder
{
    private string $endpoint;
    private Model $model;
    private array $nested     = [];
    private array $parameters = [];
    private Parser $parser;

    public function __construct(Model $model)
    {
        $this->endpoint = $model->getEndpoint();
        $this->model    = $model;
        $this->parser   = new Parser;
    }

    public function getConnection(): HttpMethodsClientInterface
    {
        return $this->getModel()->getConnection();
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    public function setEndpoint(string $endpoint): Builder
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    /***************************************************************************
     * Single retrievals
     **************************************************************************/

    public function find(int $id): ?Model
    {
        try {
            return $this->findOrFail($id);
        } catch (Throwable $error) {
            return null;
        }
    }

    public function findOrFail(int $id): ?Model
    {
        $response = $this->getConnection()->get($this->getCompiledEndpointForOne($id));

        return $this->parser->createModelFromResponse($this->model, $response);
    }

    public function first(): ?Model
    {
        return $this->limit(1)->get()->first();
    }

    /**
     * Forces the current model to refresh outside of the cache.
     */
    public function refresh(): Model
    {
        if (! isset($this->getModel()->id)) {
            throw new InvalidModelException('Cannot refresh on an unpersisted model!');
        }

        $random   = Str::random(20);
        $response = $this->getConnection()->get(
            $this->getEndpoint() . '/' . $this->getModel()->id . '?refresh=' . $random
        );

        return $this->getModel()->forceFill(
            $this->parser->createModelFromResponse($this->model, $response)->getAttributes()
        );
    }

    /***************************************************************************
     * Mass retrievals
     **************************************************************************/

    public function get(): Collection
    {
        $response = $this->getConnection()->get($this->getCompiledEndpoint());

        return $this->parser->createModelCollectionFromResponse($this->model, $response);
    }

    public function paginate(int $page = 1)
    {
        $this->parameters['page[number]'] = $page;

        $response = $this->getConnection()->get($this->getCompiledEndpoint());
        $items    = $this->parser->createModelCollectionFromResponse($this->model, $response);
        $body     = json_decode($response->getBody(), true);
        $nextPage = null;
        $prevPage = null;

        if (isset($body['meta']['next-page'])) {
            $nextPage = $body['meta']['next-page'];
        }

        if (isset($body['meta']['prev-page'])) {
            $prevPage = $body['meta']['prev-page'];
        }

        return new Paginator($this, $items, $nextPage, $prevPage);
    }

    /***************************************************************************
     * Filters
     **************************************************************************/

    /**
     * Transforms the queries array into http query parameters that are attached
     * to the endpoint.
     */
    public function getCompiledEndpoint(): string
    {
        $path   = $this->getEndpoint() . '/' . join('/', $this->nested);
        $params = [];

        foreach ($this->parameters as $key => $value) {
            if (is_array($value)) {
                $value = join(',', $value);
            }

            $params[] = "{$key}={$value}";
        }

        if ($params) {
            $params = '?' . join('&', $params);
        } else {
            $params = '';
        }

        return "{$path}{$params}";
    }

    /**
     * Transforms the queries array into http query parameters that are attached
     * to the endpoint when retrieving a single entity.
     */
    public function getCompiledEndpointForOne(int $id): string
    {
        $path   = $this->getEndpoint() . '/' . $id;
        $params = [];

        if (isset($this->parameters['include'])) {
            $params[] = 'include=' . join(',', $this->parameters['include']);
        }

        if ($params) {
            $params = '?' . join('&', $params);
        } else {
            $params = '';
        }

        return "{$path}{$params}";
    }

    public function limit(int $limit)
    {
        if ($limit < 1 || $limit > 1000) {
            throw new InvalidLimitException('When calling "limit()" the size must be between 1 and 1000.');
        }

        $this->parameters['page[size]'] = $limit;

        return $this;
    }

    public function sort(string $key, $direction = 'asc')
    {
        $direction = strtolower($direction);

        if ($direction != 'asc' && $direction != 'desc') {
            throw new InvalidSortException('Sort direction must be "asc" or "desc"!');
        }

        $sort = ($direction == 'desc') ? '-' : '';
        $sort .= $key;

        $this->parameters['sort'] = $sort;

        return $this;
    }

    public function nested(string $path)
    {
        $path           = trim($path, '/');
        $this->nested[] = $path;

        return $this;
    }

    public function with(string $relationship)
    {
        if (in_array(strtolower($relationship), ['tag', 'tags'])) {
            $relationship = 'related_items';
        }

        $this->parameters['include'][] = $relationship;

        return $this;
    }

    public function where(string $key, $value)
    {
        if (is_array($value)) {
            throw new \Exception('Value must not be an array!');
        }

        $key      = Str::snake(Str::camel($key));
        $queryKey = "filter[$key]";
        $value    = str_replace(',', '\\,', $value);

        if (! isset($this->parameters[$queryKey])) {
            $this->parameters[$queryKey] = [];
        }

        $this->parameters[$queryKey][] = $value;

        return $this;
    }

    public function whereIn(string $key, array $list)
    {
        foreach ($list as $value) {
            $this->where($key, $value);
        }

        return $this;
    }

    public function toBase(): Builder
    {
        return new static($this->getModel());
    }
}
