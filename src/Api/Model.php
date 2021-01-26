<?php

namespace Anteris\ITGlue\Api;

use Anteris\ITGlue\Orm\Model as BaseModel;

class Model extends BaseModel
{
    protected string $endpoint = 'models';

    protected $casts = [
        'created_at' => 'date',
        'updated_at' => 'date',
    ];

    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class);
    }
}
