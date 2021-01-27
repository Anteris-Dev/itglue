<?php

namespace Anteris\ITGlue\Parser;

use Anteris\ITGlue\Orm\Model;
use Anteris\ITGlue\Parser\Dto\EntityData;
use Anteris\ITGlue\Parser\Dto\RelationshipData;
use Anteris\ITGlue\Support\Arr;
use Anteris\ITGlue\Support\Exception\InvalidRelationshipException;
use Anteris\ITGlue\Support\Exception\InvalidResponseException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;

class Parser
{
    /**
     * Converts an Http response into an ORM model.
     */
    public function createModelFromResponse(Model $model, ResponseInterface $response)
    {
        $json   = json_decode($response->getBody(), true);
        $entity = $this->createEntityFromArray($json);

        return $this->createModelFromEntity($model->newInstance(), $entity);
    }

    /**
     * Converts an Http response into a collection of models.
     */
    public function createModelCollectionFromResponse(
        Model $model,
        ResponseInterface $response
    ): Collection {
        $json     = json_decode($response->getBody(), true);
        $entities = $this->createEntitiesFromArray($json);

        $collection = new Collection;

        foreach ($entities as $entity) {
            $collection[] = $this->createModelFromEntity($model, $entity);
        }

        return $collection;
    }

    /**
     * Creates a model from an entity DTO.
     */
    public function createModelFromEntity(Model $model, EntityData $entity): Model
    {
        $model     = $model->newInstance(Arr::snakeKeys($entity->attributes));
        $model->id = $entity->id;

        // Fill the relationships into the model.
        $relations = [];

        foreach ($entity->included as $relationship) {
            $method   = Str::camel($relationship->type);
            $singular = false;

            // Try the singular version for single relationships
            if (! method_exists($model, $method)) {
                $method   = Str::singular($method);
                $singular = true;

                if (! method_exists($model, $method)) {
                    throw new InvalidRelationshipException(
                        'Unable to find relationship: ' . $method
                    );
                }
            }

            $relatedModel = $model->{$method}()->getModel()->newInstance();
            $relatedModel = $this->createModelFromEntity($relatedModel, $relationship);

            if ($singular) {
                $relations[$method] = $relatedModel;

                continue;
            }

            if (! isset($relations[$method])) {
                $relations[$method] = new Collection;
            }
   
            $relations[$method][] = $relatedModel;
        }

        $model->setRelations($relations);

        return $model;
    }

    /**
     * Creates an entity from an array.
     */
    public function createEntityFromArray(array $array): EntityData
    {
        // Convert any relationships into easy to work with DTOs
        $includes = $this->buildEntities($array['included'] ?? []);
        $includes = $this->attachIncludesToEntities($includes, $includes);

        // Convert the resource into a single entity
        $data = $this->buildEntity($array['data'] ?? []);
        $data = $this->attachIncludesToEntity($includes, $data);

        return $data;
    }

    /**
     * Creates multiple entities from an array.
     */
    public function createEntitiesFromArray(array $array): Collection
    {
        // Convert any relationships into easy to work with DTOs
        $includes = $this->buildEntities($array['included'] ?? []);
        $includes = $this->attachIncludesToEntities($includes, $includes);

        // Convert the resource into multiple entities
        $data = $this->buildEntities($array['data'] ?? []);
        $data = $this->attachIncludesToEntities($includes, $data);

        return $data;
    }

    /***************************************************************************
     * Relationships
     **************************************************************************/

    /**
     * Attaches any included relationships to the entity passed.
     */
    protected function attachIncludesToEntity(
        Collection $includes,
        EntityData $entity
    ): EntityData {
        // This section has to check the default type and the plural version
        // because IT Glue is not consistent.
        foreach ($entity->related as $related) {
            $entity->included = $entity->included->merge(
                $includes
                    ->whereIn('type', [ $related->type, Str::plural($related->type) ])
                    ->where('id', $related->id)
            );
        }

        return $entity;
    }

    /**
     * Attaches any included relationships to the entities passed.
     */
    protected function attachIncludesToEntities(
        Collection $includes,
        Collection $entities
    ): Collection {
        foreach ($entities as $key => $entity) {
            $entities[$key] = $this->attachIncludesToEntity($includes, $entity);
        }

        return $entities;
    }

    /***************************************************************************
     * Builders
     **************************************************************************/

    /**
     * Builds the entity from an array.
     */
    protected function buildEntity(array $entity): EntityData
    {
        $this->validateSingleEntity($entity);

        $relationships = $entity['relationships'] ?? [];
        $related       = new Collection;

        // Iterates through the relationships on the resource and builds DTOs
        // for them so they can be validly passed to the entity DTO.
        foreach ($relationships as $relationship) {
            if (! isset($relationship['data'])) {
                continue;
            }

            // Annoyingly, IT Glue does not always wrap the contents in an array
            // here we wrap it if necessary.
            if (
                is_array($relationship['data']) &&
                isset($relationship['data']['id']) &&
                isset($relationship['data']['type'])
            ) {
                $relationship['data'] = [$relationship['data']];
            }

            foreach ($relationship['data'] as $relationshipData) {
                if (! is_array($relationshipData)) {
                    continue;
                }

                if (in_array($relationshipData['type'], ['related-items', 'related-item'])) {
                    $relationshipData['type'] = 'tags';
                }

                $related[] = new RelationshipData($relationshipData);
            }
        }

        if (in_array($entity['type'], ['related-items', 'related-item'])) {
            $entity['type'] = 'tags';
        }

        return new EntityData([
            'id'         => $entity['id'],
            'attributes' => $entity['attributes'],
            'included'   => new Collection,
            'related'    => $related,
            'type'       => $entity['type'],
        ]);
    }

    /**
     * Builds multiple entities from an array.
     */
    protected function buildEntities(array $entities): Collection
    {
        $this->validateMultipleEntities($entities);
        
        $entityCollection = new Collection;
        
        foreach ($entities as $entity) {
            $entityCollection[] = $this->buildEntity($entity);
        }

        return $entityCollection;
    }

    /***************************************************************************
     * Validators
     **************************************************************************/

    /**
     * Validates that the array passed contains a single entity.
     */
    protected function validateSingleEntity(array $array)
    {
        if (
            ! isset($array['id']) ||
            ! isset($array['attributes']) ||
            ! isset($array['type'])
        ) {
            throw new InvalidResponseException(
                'We were unable to detect an entity in the repsonse!'
            );
        }
    }

    /**
     * Validates that the array passed contains multiple entities.
     */
    protected function validateMultipleEntities(array $array)
    {
        if (
            isset($array['id']) ||
            isset($array['attributes']) ||
            isset($array['type'])
        ) {
            throw new InvalidResponseException(
                'We were unable to detect any entities in the response!'
            );
        }
    }
}
