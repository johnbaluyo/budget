<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GAAProject extends Model
{
    protected $fillable = [
        'project_id',
        'gaa_id',
    ];

    protected $table = "gaa_project";

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function gaa()
    {
        return $this->belongsTo(GAA::class, 'gaa_id', 'id');
    }

    public function expenses()
    {
        return $this->hasMany(GAAProjectExpenses::class, 'gaa_project_id', 'id');
    }
}
