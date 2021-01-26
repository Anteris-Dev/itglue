<?php

namespace Anteris\ITGlue\Orm;

use Illuminate\Support\Collection;

class Paginator
{
    public Collection $items;

    protected Builder $query;
    protected ?int $nextPage;
    protected ?int $prevPage;

    public function __construct(Builder $query, Collection $items, ?int $nextPage = null, ?int $prevPage = null)
    {
        $this->query    = $query;
        $this->items    = $items;
        $this->nextPage = $nextPage;
        $this->prevPage = $prevPage;
    }

    public function hasNextPage()
    {
        return ($this->nextPage !== null);
    }

    public function hasPrevPage()
    {
        return ($this->prevPage !== null);
    }

    public function nextPage()
    {
        return $this->query->paginate($this->nextPage);
    }

    public function prevPage()
    {
        return $this->query->paginate($this->prevPage);
    }
}
