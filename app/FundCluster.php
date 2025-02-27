<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FundCluster extends Model
{
    protected $fillable = [
        'code', 'name', 
    ];

    protected $table = "fundclusters";
}
