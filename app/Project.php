<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'project_name',
        'div_id',
        'approved_budget_id',
    ];

    protected $table = "projects";

    public function approvedBudget()
    {
        return $this->belongsTo(ApprovedBudget::class, 'approved_budget_id', 'id');
    }

    public function gaaProjects()
    {
        return $this->hasMany(GAAProject::class, 'project_id', 'id');
    }

    public function division()
    {
        return $this->belongsTo(Division::class, 'div_id', 'id');
    }

    public function monthlyBudgets()
    {
        return $this->hasMany(ProjectMonthlyBudget::class, 'project_id', 'id');
    }
}
