<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApprovedBudget extends Model
{
    protected $fillable = [
        'grand_total_amount',
        'year',
    ];

    protected $table = "approved_budget";


    public function projects()
    {
        return $this->hasMany(Project::class, 'approved_budget_id', 'id');
    }

    public function gaa()
    {
        return $this->hasMany(GAA::class, 'approved_budget_id', 'id');
    }
}
