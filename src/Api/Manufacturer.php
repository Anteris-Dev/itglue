<?php

namespace Anteris\ITGlue\Api;

use Anteris\ITGlue\Orm\Model as BaseModel;

class Manufacturer extends BaseModel
{
    protected string $endpoint = 'manufacturers';

    protected $casts = [
        'created_at' => 'date',
        'updated_at' => 'date',
    ];

    public function models()
    {
        return $this->hasMany(Model::class);
    }
}
