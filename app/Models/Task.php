<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'sprint_id',
        'number',
        'title',
        'description',
        'status',
        'priority',
        'created_by',
        'start_at',
        'due_at',
        'completed_at',
        'overdue_at',
        'reminded_at',
        'position',
        'rank',
        'score',
        'feedback',
        'result_link',
        'result_links',
        'template_id',
    ];

    protected $casts = [
        'status' => TaskStatus::class,
        'priority' => TaskPriority::class,
        'start_at' => 'datetime',
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
        'overdue_at' => 'datetime',
        'reminded_at' => 'datetime',
        'result_links' => 'array',
    ];

    /** Kode tugas yang dirujuk di chat, misalnya T-12. */
    public function getCodeAttribute(): string
    {
        return 'T-' . $this->number;
    }

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function sprint()
    {
        return $this->belongsTo(Sprint::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function template()
    {
        return $this->belongsTo(TaskTemplate::class, 'template_id');
    }

    public function assignees()
    {
        return $this->belongsToMany(Intern::class, 'task_assignees')->withTimestamps();
    }

    public function checklistItems()
    {
        return $this->hasMany(TaskChecklistItem::class)->orderBy('position');
    }

    public function labels()
    {
        return $this->belongsToMany(TaskLabel::class, 'task_label', 'task_id', 'label_id');
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class);
    }

    public function activities()
    {
        return $this->hasMany(TaskActivity::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function logbooks()
    {
        return $this->hasMany(Logbook::class);
    }
}
