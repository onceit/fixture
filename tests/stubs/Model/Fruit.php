<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;

class Fruit extends Model
{
    protected $tableName = 'fruits';
    
    protected $timestamps = true;
}
