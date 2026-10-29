<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workspace extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'team_id',
        'intern_id',
        'name',
        'color',
        'description',
        'slug',
        'is_archived',
        'finished_at',
        'created_by',
        'mentor_user_id',
    ];

    protected $casts = [
        'is_archived' => 'boolean',
        'finished_at' => 'datetime',
    ];

    /**
     * Tim pemilik label tugas: tim workspace, tim intern (Space), atau tim mentor pemilik (kelompok).
     */
    public function labelTeamId(): ?int
    {
        if ($this->team_id) {
            return $this->team_id;
        }

        if ($this->type === 'group') {
            return Mentor::where('user_id', $this->mentor_user_id)->value('team_id');
        }

        $intern = $this->intern_id ? Intern::with('mentor')->find($this->intern_id) : null;

        return $intern?->team_id ?? $intern?->mentor?->team_id;
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function intern()
    {
        return $this->belongsTo(Intern::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'workspace_members')
            ->withPivot(['role', 'joined_at', 'left_at'])
            ->withTimestamps();
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function sprints()
    {
        return $this->hasMany(Sprint::class)->orderBy('id');
    }

    public function channels()
    {
        return $this->hasMany(Channel::class);
    }
}
