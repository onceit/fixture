<?php

namespace Codesleeve\Fixture\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class Crew extends Model
{
    protected $table = 'crew';

    public $timestamps = false;

    public function boat()
    {
        return $this->belongsTo(__NAMESPACE__ . '\\Boat');
    }
}
