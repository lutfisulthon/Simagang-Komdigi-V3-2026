<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

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
        'story_points',
        'epic_id',
        'score',
        'feedback',
        'result_link',
        'result_links',
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

    /**
     * Frontend mengirim waktu sebagai ISO 8601 UTC (`...Z`). Kolom datetime dibaca sebagai zona waktu
     * aplikasi, jadi nilai bertanda zona wajib dikonversi dulu; kalau tidak, jam UTC tersimpan apa adanya
     * dan deadline bergeser sebesar selisih zona (WITA: -8 jam) setiap kali tugas dibuat atau disunting.
     */
    public function setStartAtAttribute($value): void
    {
        $this->attributes['start_at'] = $this->toAppTimezone($value);
    }

    public function setDueAtAttribute($value): void
    {
        $this->attributes['due_at'] = $this->toAppTimezone($value);
    }

    private function toAppTimezone($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $date = $value instanceof \DateTimeInterface ? Carbon::instance($value) : Carbon::parse($value);

        return $date->setTimezone(config('app.timezone'))->format($this->getDateFormat());
    }

    /**
     * Definisi tunggal "terlambat": belum selesai dan sudah lewat deadline. `overdue_at` hanya penanda yang diisi
     * job terjadwal (tiap jam), jadi tidak boleh jadi satu-satunya acuan: tanpa cron, atau sebelum job berikutnya
     * jalan, dashboard dan laporan menampilkan 0 padahal board sudah menandai tugasnya terlambat.
     */
    public function isLate(): bool
    {
        if ($this->status === TaskStatus::Done) {
            return false;
        }

        return $this->overdue_at !== null || ($this->due_at !== null && $this->due_at->isPast());
    }

    /** Padanan isLate() untuk query Eloquent. */
    public function scopeLate($query)
    {
        return $query->where('status', '!=', TaskStatus::Done->value)
            ->where(fn ($w) => $w->whereNotNull('overdue_at')->orWhere('due_at', '<', now()));
    }

    /** Padanan isLate() untuk agregat SQL mentah. `$p` = awalan kolom, mis. "t." atau "tasks.". Nilai waktu dibuat server. */
    public static function lateSql(string $p = ''): string
    {
        $now = now()->format('Y-m-d H:i:s');

        return "({$p}status != 'done' and ({$p}overdue_at is not null or {$p}due_at < '{$now}'))";
    }

    /** Kode tugas yang dirujuk di chat, misalnya T-12. */
    public function getCodeAttribute(): string
    {
        return 'T-' . $this->number;
    }

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function epic()
    {
        return $this->belongsTo(Epic::class);
    }

    public function sprint()
    {
        return $this->belongsTo(Sprint::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
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
