<?php

namespace Anteris\ITGlue\Parser\Dto;

use Illuminate\Support\Collection;
use Spatie\DataTransferObject\DataTransferObject;

class EntityData extends DataTransferObject
{
    public $id;
    public string $type;
    public array $attributes;
    public ?Collection $included;
    public ?Collection $related;
}
