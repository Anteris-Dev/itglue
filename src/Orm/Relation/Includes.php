<?php

namespace Anteris\ITGlue\Orm\Relation;

use Anteris\ITGlue\Orm\Builder;
use Anteris\ITGlue\Orm\Model;
use Anteris\ITGlue\Support\Str;
use Illuminate\Support\Collection;

/**
 * By far the most hacky relationship in the family. We have to reload the
 * originator with the relationship included, then we have to check for its existence
 * before returning.
 *
 * Because of this, we don't dare call the parent constructor, because everything
 * is changed up.
 */
class Includes extends AbstractRelation
{
    protected string $includeName;
    protected string $method;

    public function __construct(
        Builder $query,
        Model $child,
        string $idKey,
        string $includeName,
        string $method
    ) {
        $this->query       = $query;
        $this->related     = $child;
        $this->idKey       = $idKey;
        $this->includeName = $includeName;
        $this->method      = $method;
        $this->originator  = $query->getModel();
    }

    public function getResults()
    {
        $id = $this->originator->{$this->idKey};

        /** @var Model */
        $result = $this->query->toBase()
            ->with($this->includeName)
            ->find($id);

        // Check for the existence of the relationship so we don't loop through
        // again in an attempt to retrieve the relationship once more.
        $camelIncludeName = Str::camel($this->includeName);

        if (
            $result != null &&
            (
                $result->relationIsLoaded($camelIncludeName) ||
                $result->relationIsLoaded($this->method)
            )
        ) {
            return $result->{$this->method};
        }

        return new Collection([]);
    }
}
