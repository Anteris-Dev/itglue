<?php

namespace Anteris\ITGlue\Orm;

use Anteris\ITGlue\Connection;
use Anteris\ITGlue\Orm\Relation\AbstractRelation;
use Anteris\ITGlue\Orm\Relation\BelongsTo;
use Anteris\ITGlue\Orm\Relation\HasMany;
use Anteris\ITGlue\Orm\Relation\Includes;
use Anteris\ITGlue\Parser\Parser;
use Anteris\ITGlue\Support\Arr;
use Anteris\ITGlue\Support\Exception\InvalidModelException;
use Carbon\Carbon;
use Http\Client\Common\HttpMethodsClientInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Jenssegers\Model\Model as BaseModel;
use Throwable;

class Model extends BaseModel
{
    /** @var string The name of the connection that should be used when interacting with this model. */
    protected string $connection = 'default';

    /** @var string The endpoint this model represents. */
    protected string $endpoint;

    /** @var array The loaded relations for the model. */
    protected array $relations = [];

    public function newInstance($attributes = []): Model
    {
        return parent::newInstance($attributes);
    }

    /**
     * Returns an instance of the model's current connection.
     */
    public function getConnection(): HttpMethodsClientInterface
    {
        return Connection::get($this->connection);
    }

    /**
     * Sets the name of the model's connection.
     */
    public function setConnection(string $name): void
    {
        $this->connection = $name;
    }

    /**
     * Returns the model's endpoint.
     */
    public function getEndpoint(): string
    {
        if (! isset($this->endpoint)) {
            throw new InvalidModelException(static::class . " does not have an endpoint defined!");
        }

        return $this->endpoint;
    }

    /***************************************************************************
     * Persistence
     **************************************************************************/

    public function exists(): bool
    {
        return ($this->id !== null);
    }

    public function create(): static
    {
        if ($this instanceof ReadOnly) {
            throw new InvalidModelException(static::class . ' is a read only resource!');
        }

        if ($this instanceof IncludeOnly) {
            throw new InvalidModelException(static::class . ' can only be read through other resources!');
        }

        $response = $this->getConnection()->post(
            $this->getEndpoint(),
            [],
            $this->toJson()
        );

        $newModel = (new Parser)->createModelFromResponse($this, $response);

        // Fill the current model in case it is in use
        $this->forceFill($newModel->getAttributes());

        return $newModel;
    }

    public function update(): static
    {
        if ($this instanceof ReadOnly) {
            throw new InvalidModelException(static::class . ' is a read only resource!');
        }

        if ($this instanceof IncludeOnly) {
            throw new InvalidModelException(static::class . ' can only be read through other resources!');
        }

        $this->getConnection()->patch(
            $this->endpoint . "/{$this->id}",
            [],
            $this->toJson()
        );

        // Refresh so as to persist the cache instance
        return $this->refresh();
    }

    public function save(): static
    {
        if (! $this->exists()) {
            return $this->create();
        }

        return $this->update();
    }

    /***************************************************************************
     * Attributes
     **************************************************************************/

    /**
     * Overrides the parent caster to implement date / time abilities.
     */
    public function castAttribute($key, $value)
    {
        switch ($this->getCastType($key)) {
            case 'date':
            case 'datetime':
                return new Carbon($value);
            default:
                return parent::castAttribute($key, $value);
        }
    }

    /**
     * Overrides the parent getter to implement relationship getting as well.
     */
    public function getAttribute($key)
    {
        if (array_key_exists($key, $this->attributes)) {
            return parent::getAttribute($key);
        }

        // Avoid passing on to a helper method of ours...
        if (method_exists(self::class, $key)) {
            return;
        }

        return $this->getRelationValue($key);
    }

    /***************************************************************************
     * Queries
     **************************************************************************/

    /**
     * Attempts to return all records of this model type.
    */
    public static function all(): Collection
    {
        $all  = new Collection;
        $page = static::query()->limit(1000)->paginate();

        while (true) {
            $all = $all->merge($page->items);

            if (! $page->hasNextPage()) {
                break;
            }

            $page = $page->nextPage();
        }

        return $all;
    }

    public static function query()
    {
        return (new static)->newQuery();
    }

    public function newQuery()
    {
        if ($this instanceof WriteOnly) {
            throw new InvalidModelException(static::class . ' is a write only resource!');
        }

        if ($this instanceof IncludeOnly) {
            throw new InvalidModelException(static::class . ' can only be read through other resources!');
        }

        return new Builder($this);
    }

    /***************************************************************************
     * Relationships
     **************************************************************************/

    public function newRelatedInstance(string $model)
    {
        if (! is_subclass_of($model, Model::class)) {
            throw new \Exception("Invalid model: $model!");
        }

        return new $model;
    }

    public function getForeignKey(): string
    {
        return Str::snake(
            substr(strrchr(static::class, '\\'), 1)
        ) . '_id';
    }

    public function getRelationValue($key)
    {
        if ($this->relationIsLoaded($key)) {
            return $this->relations[$key];
        }

        if (method_exists($this, $key)) {
            return $this->getRelationValueFromMethod($key);
        }
    }

    protected function getRelationValueFromMethod($method)
    {
        $relation = $this->{$method}();

        if ($relation == null) {
            throw new \Exception('Relationship returned "null". Was return called?');
        }
        
        if (! $relation instanceof AbstractRelation) {
            throw new \Exception('Invalid relationship returned!');
        }

        try {
            $results = $relation->getResults();

            $this->setRelation($method, $results);
        } catch (Throwable $error) {
            return null;
        }

        return $results;
    }

    public function setRelation(string $key, $value): Model
    {
        $this->relations[$key] = $value;
        
        return $this;
    }

    public function setRelations(array $value): Model
    {
        $this->relations = $value;

        return $this;
    }

    public function relationIsLoaded($key): bool
    {
        return array_key_exists($key, $this->relations);
    }

    public function belongsTo(
        string $model,
        ?string $idKey = null
    ) {
        $model = $this->newRelatedInstance($model);

        if ($idKey == null) {
            $idKey = $model->getForeignKey();
        }

        return new BelongsTo($model->query(), $this, $idKey);
    }

    public function hasMany(
        string $model,
        string $idKey = 'id',
        ?string $route = null
    ): HasMany {
        $model = $this->newRelatedInstance($model);

        if ($route == null) {
            [$one, $caller] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
            $route          = Str::snake($caller['function']);
        }

        return new HasMany($model->query(), $this, $idKey, $route);
    }

    public function includes(
        string $model,
        string $idKey = 'id',
        ?string $includeName = null
    ): Includes {
        $model = $this->newRelatedInstance($model);

        // Determine the method that was called
        [$one, $caller] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $method         = $caller['function'];

        // If no include name, create it from the method
        if ($includeName == null) {
            $includeName = Str::kebab($method);
        }

        return new Includes($this->newQuery(), $model, $idKey, $includeName, $method);
    }

    /***************************************************************************
     * Serialization
     **************************************************************************/

    public function jsonSerialize()
    {
        return $this->jsonSerializeArray($this->toArray());
    }

    public function jsonSerializeArray(array $data)
    {
        $serialized = [
            'type' => $this->endpoint,
        ];

        if ($this->id != null) {
            $serialized['id'] = $this->id;
            unset($data['id']);
        }

        $serialized['attributes'] = Arr::kebabKeys($data);

        return [
            'data' => $serialized,
        ];
    }

    /***************************************************************************
     * Call Forwarders
     **************************************************************************/

    public function __call($name, $arguments)
    {
        return $this->newQuery()->{$name}(...$arguments);
    }

    public static function __callStatic($method, $parameters)
    {
        return (new static)->{$method}(...$parameters);
    }
}
