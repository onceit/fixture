<?php

namespace Codesleeve\Fixture\Tests\Drivers\Fixtures;

use Illuminate\Database\Eloquent\Model;

class Parrot extends Model
{
    protected $table = 'parrots';

    public $timestamps = false;

    public function pirate()
    {
        return $this->belongsTo(__NAMESPACE__ . '\\Pirate');
    }
}
