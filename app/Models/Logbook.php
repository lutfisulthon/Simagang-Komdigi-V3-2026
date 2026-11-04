<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Logbook extends Model
{
    use HasFactory;

    protected $fillable = [
        'intern_id',
        'task_id',
        'date',
        'activity',
        'photo_path',
        'approval_status',
        'approved_by',
        'approved_at',
        'approval_note',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function intern()
    {
        return $this->belongsTo(Intern::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function comments()
    {
        return $this->hasMany(MentorComment::class);
    }

    public function approver()
    {
        return $this->belongsTo(Mentor::class, 'approved_by');
    }
}