<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinalReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'intern_id',
        'file_path',
        'project_file',
        'project_file_name',
        'project_files',
        'project_link',
        'project_links',
        'file_name',
        'status',
        'activities',
        'grade',
        'score',
        'needs_revision',
        'admin_note',
        'submitted_at',
        'include_task_recap',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'needs_revision' => 'boolean',
        'include_task_recap' => 'boolean',
        'activities' => 'array',
        'project_files' => 'array',
        'project_links' => 'array',
    ];

    public function intern()
    {
        return $this->belongsTo(Intern::class);
    }

    public function testimonial()
    {
        return $this->hasOne(Testimonial::class);
    }

    public function testimonials()
    {
        return $this->hasMany(Testimonial::class);
    }
}