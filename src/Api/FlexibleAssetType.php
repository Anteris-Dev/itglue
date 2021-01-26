<?php

namespace Anteris\ITGlue\Api;

use Anteris\ITGlue\Orm\Model;

class FlexibleAssetType extends Model
{
    protected string $endpoint = 'flexible_asset_types';

    protected $casts = [
        'created_at' => 'date',
        'updated_at' => 'date',
    ];

    public function fields()
    {
        return $this->hasMany(FlexibleAssetField::class, 'id', 'flexible_asset_fields');
    }
}
