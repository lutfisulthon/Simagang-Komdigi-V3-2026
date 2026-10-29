<?php

namespace App\Notifications;

use App\Models\Sprint;
use Illuminate\Notifications\Notification;

class SprintToDivideNotification extends Notification
{
    use \App\Notifications\Concerns\RespectsPreferences;

    public function preferenceType(): string
    {
        return 'sprint_to_divide';
    }

    /** @param  array<int, string>  $titles  judul tugas yang belum dibagi (maks. 5) */
    public function __construct(private Sprint $sprint, private int $count, private array $titles)
    {
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => $this->preferenceType(),
            'sprint_id' => $this->sprint->id,
            'sprint_name' => $this->sprint->name,
            'workspace_id' => $this->sprint->workspace_id,
            'task_count' => $this->count,
            'task_titles' => array_slice($this->titles, 0, 5),
            'message' => "{$this->count} tugas menunggu Anda bagi di {$this->sprint->name}",
        ];
    }
}
