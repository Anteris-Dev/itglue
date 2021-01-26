<?php

namespace Anteris\ITGlue\Orm\Relation;

use Anteris\ITGlue\Orm\Model;

class BelongsTo extends AbstractRelation
{
    public function getResults(): ?Model
    {
        $id = $this->originator->{$this->idKey};

        if (! $id) {
            return null;
        }

        return $this->query->find($id);
    }
}
