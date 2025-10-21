<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectMonthlyBudget extends Model
{
    protected $fillable = [
        'project_id',
        'month',
        'year',
        'budget_amount',
    ];

    protected $table = "project_monthly_budgets";

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }
}
