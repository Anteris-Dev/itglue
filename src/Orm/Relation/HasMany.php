<?php

namespace Anteris\ITGlue\Orm\Relation;

use Anteris\ITGlue\Orm\Builder;
use Anteris\ITGlue\Orm\Model;
use Illuminate\Support\Collection;

class HasMany extends AbstractRelation
{
    /** @var string Represents the sub-endpoint used to access the relationship. */
    protected string $endpoint;

    public function __construct(
        Builder $query,
        Model $originator,
        string $idKey,
        string $endpoint
    ) {
        parent::__construct($query, $originator, $idKey);

        $this->endpoint = $endpoint;
    }

    public function getResults(): Collection
    {
        return $this->query->toBase()
            ->setEndpoint($this->originator->getEndpoint())
            ->nested($this->originator->{$this->idKey})
            ->nested('relationships')
            ->nested($this->endpoint)
            ->get();
    }
}
