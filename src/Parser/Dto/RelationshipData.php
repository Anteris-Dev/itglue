<?php

namespace Anteris\ITGlue\Parser\Dto;

use Spatie\DataTransferObject\DataTransferObject;

class RelationshipData extends DataTransferObject
{
    public $id;
    public string $type;
}
