<?php

namespace Codesleeve\Fixture\Tests\Drivers\Fixtures;

use Illuminate\Database\Eloquent\Model;

class Catchphrase extends Model
{
    protected $table = 'catchphrases';

    public $timestamps = false;

    public function pirates()
    {
        return $this->belongsToMany(__NAMESPACE__ . '\\Pirate', 'catchphrases_pirates');
    }
}
