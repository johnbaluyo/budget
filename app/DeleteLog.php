<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeleteLog extends Model
{
    protected $table = 'z_delete_logs';

    protected $fillable = [
        'table_name',
        'user_id',
        'delete_log',
    ];

    public function delete_log($table, $user_id, $delete_log)
    {
        DeleteLog::create([
            'table_name' => $table,
            'user_id' => $user_id,
            'delete_log' => $delete_log,
        ]);
    }
}
