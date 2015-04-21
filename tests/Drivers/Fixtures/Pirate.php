<?php

namespace Codesleeve\Fixture\Tests\Drivers\Fixtures;

use Illuminate\Database\Eloquent\Model;

class Pirate extends Model
{
    protected $table = 'pirates';

    public $timestamps = true;

    public function parrot()
    {
        return $this->hasOne( __NAMESPACE__ . '\\Parrot');
    }
}
