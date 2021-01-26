<?php

namespace Anteris\ITGlue\Api;

use Anteris\ITGlue\Orm\Model;
use Anteris\ITGlue\Orm\WriteOnly;

class RelatedItem extends Model implements WriteOnly
{
    protected string $endpoint = 'related_items';
}
