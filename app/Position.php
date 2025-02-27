<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = [
        'position_name', 'salary_grade', 'step_increment', 'plantilla_code',
    ];

    protected $table = "positions";
}
