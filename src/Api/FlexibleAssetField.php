<?php

namespace Anteris\ITGlue\Api;

use Anteris\ITGlue\Orm\Model;

class FlexibleAssetField extends Model
{
    protected string $endpoint = 'flexible_asset_fields';

    protected $casts = [
        'created_at' => 'date',
        'updated_at' => 'date',
    ];
}
