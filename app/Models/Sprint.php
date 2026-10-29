<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sprint extends Model
{
    public const PLANNED = 'planned';
    public const ACTIVE = 'active';
    public const COMPLETED = 'completed';

    protected $fillable = [
        'workspace_id',
        'name',
        'goal',
        'status',
        'start_date',
        'end_date',
        'leader_intern_id',
        'completed_at',
        'divide_reminded_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'completed_at' => 'datetime',
        'divide_reminded_at' => 'datetime',
    ];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function leader()
    {
        return $this->belongsTo(Intern::class, 'leader_intern_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
