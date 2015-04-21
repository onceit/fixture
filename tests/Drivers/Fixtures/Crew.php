<?php

namespace Codesleeve\Fixture\Tests\Drivers\Fixtures;

use Illuminate\Database\Eloquent\Model;

class Crew extends Model
{
    protected $table = 'crew';

    public function boat()
    {
        return $this->belongsTo( __NAMESPACE__ . '\\Boat');
    }
}
