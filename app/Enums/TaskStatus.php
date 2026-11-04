<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Todo = 'todo';
    case InProgress = 'in_progress';
    case Review = 'review';
    case Revision = 'revision';
    case Done = 'done';

    public function label(): string
    {
        return match ($this) {
            self::Todo => 'To Do',
            self::InProgress => 'In Progress',
            self::Review => 'Review',
            self::Revision => 'Revisi',
            self::Done => 'Selesai',
        };
    }

    /** Status yang boleh dituju intern (maksimal sampai Review). */
    public function isInternAllowed(): bool
    {
        return in_array($this, [self::Todo, self::InProgress, self::Review], true);
    }
}
