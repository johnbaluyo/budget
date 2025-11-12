<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GAAProjectMonthlyBudget extends Model
{
    protected $fillable = [
        'gaa_project_id',
        'month',
        'year',
        'budget_amount',
    ];

    protected $table = "gaa_project_monthly_budgets";

    public function gaaProject()
    {
        return $this->belongsTo(GAAProject::class, 'gaa_project_id', 'id');
    }
}
