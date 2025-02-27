<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    protected $fillable = [
        'division_name', 'division_acronym', 'location',
    ];

    protected $table = "divisions";
}
