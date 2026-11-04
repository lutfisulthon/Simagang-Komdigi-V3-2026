<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskLabel extends Model
{
    protected $fillable = [
        'team_id',
        'name',
        'color',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'task_label', 'label_id', 'task_id');
    }
}
