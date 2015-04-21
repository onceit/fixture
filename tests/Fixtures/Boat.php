<?php

namespace Codesleeve\Fixture\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class Boat extends Model
{
    protected $table = 'boats';

    public $timestamps = false;

    public function crew()
    {
        return $this->hasMany(__NAMESPACE__ . '\\Crew');
    }
}
