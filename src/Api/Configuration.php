<?php

namespace Anteris\ITGlue\Api;

use Anteris\ITGlue\Orm\Model;

class Configuration extends Model
{
    protected string $endpoint = 'configurations';

    protected $casts = [
        'created_at' => 'date',
        'updated_at' => 'date',
    ];

    public function attachments()
    {
        return $this->includes(Attachment::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function interfaces()
    {
        return $this->hasMany(ConfigurationInterface::class, 'id', 'configuration_interfaces');
    }

    public function passwords()
    {
        return $this->includes(Password::class);
    }

    public function tags()
    {
        return $this->includes(RelatedItem::class, 'id', 'related_items');
    }

    public function rmmRecords()
    {
        return $this->includes(RMMRecord::class);
    }

    public function tickets()
    {
        return $this->includes(Ticket::class);
    }

    public function type()
    {
        return $this->belongsTo(ConfigurationType::class);
    }
}
