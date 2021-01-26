<?php

namespace Anteris\ITGlue\Orm\Relation;

use Anteris\ITGlue\Orm\Builder;
use Anteris\ITGlue\Orm\Model;

abstract class AbstractRelation
{
    /** @var Builder A query on the related model. */
    protected Builder $query;

    /** @var Model The model that is being connected to the originator. */
    protected Model $related;

    /** @var Model The model that originally defined the relationship. */
    protected Model $originator;
    
    /** @var string Represents the key name for the ID we will use to access the relationship. */
    protected string $idKey;

    public function __construct(Builder $query, Model $originator, string $idKey)
    {
        $this->query      = $query;
        $this->related    = $query->getModel();
        $this->originator = $originator;
        $this->idKey      = $idKey;
    }

    public function getModel(): Model
    {
        return $this->related;
    }

    abstract public function getResults();
}
