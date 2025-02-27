<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GAAProjectExpenses extends Model
{
    protected $fillable = [
        'gaa_project_id',
        'type',
        'amount',
        'remarks',
        'date',
        'division_id',
        'realign_from',
        'realign_to',
    ];

    protected $table = "gaa_project_expenses";

    public function gaaProject()
    {
        return $this->belongsTo(GAAProject::class, 'gaa_project_id', 'id');
    }
}
