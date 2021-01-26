<?php

namespace Anteris\ITGlue\Api;

use Anteris\ITGlue\Orm\Model;
use Anteris\ITGlue\Orm\WriteOnly;

class Attachment extends Model implements WriteOnly
{
    protected string $endpoint = 'attachments';
}
