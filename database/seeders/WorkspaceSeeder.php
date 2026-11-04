<?php

namespace Database\Seeders;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Channel;
use App\Models\Intern;
use App\Models\Message;
use App\Models\Task;
use App\Models\TaskActivity;
use App\Models\TaskComment;
use App\Models\TaskLabel;
use App\Models\Team;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Data contoh modul Workspace. Dibuat langsung (tanpa WorkspaceProvisioner)
 * dan idempoten: aman dijalankan ulang.
 */
class WorkspaceSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::where('email', 'admin@simagang.com')->value('id');

        $announcement = Channel::firstOrCreate(
            ['workspace_id' => null, 'type' => 'announcement'],
            ['name' => 'pengumuman', 'created_by' => $adminId]
        );

        $fallbackTeam = Team::orderBy('id')->first();
        if (! $fallbackTeam) {
            $this->command->warn('Tidak ada data teams, WorkspaceSeeder dilewati.');
            return;
        }

        $interns = Intern::with(['user', 'mentor.user'])
            ->where('is_active', true)
            ->whereNotNull('mentor_id')
            ->get();

        foreach ($interns as $intern) {
            $mentorUser = $intern->mentor?->user;
            if (! $intern->user || ! $mentorUser) {
                continue;
            }

            $team = Team::find($intern->team_id ?? $intern->mentor->team_id) ?? $fallbackTeam;

            $teamWorkspace = $this->teamWorkspace($team, $adminId);
            $teamWorkspace->members()->syncWithoutDetaching([
                $mentorUser->id => ['role' => 'mentor', 'joined_at' => now()],
                $intern->user->id => ['role' => 'member', 'joined_at' => now()],
            ]);

            $teamChannel = Channel::firstOrCreate(
                ['workspace_id' => $teamWorkspace->id, 'type' => 'team'],
                ['name' => 'umum-' . Str::slug($team->name), 'created_by' => $adminId]
            );
            $teamChannel->members()->syncWithoutDetaching([$mentorUser->id, $intern->user->id]);
            $announcement->members()->syncWithoutDetaching([$mentorUser->id, $intern->user->id]);

            $space = Workspace::firstOrCreate(
                ['type' => 'intern', 'intern_id' => $intern->id],
                [
                    'name' => 'Space ' . $intern->name,
                    'slug' => 'space-' . $intern->id . '-' . Str::slug($intern->name),
                    'created_by' => $adminId,
                ]
            );
            $space->members()->syncWithoutDetaching([
                $mentorUser->id => ['role' => 'mentor', 'joined_at' => now()],
                $intern->user->id => ['role' => 'owner', 'joined_at' => now()],
            ]);

            $dm = Channel::firstOrCreate(
                ['workspace_id' => $space->id, 'type' => 'direct'],
                ['name' => 'dm-' . Str::slug($intern->name), 'is_private' => true, 'created_by' => $adminId]
            );
            $dm->members()->syncWithoutDetaching([$mentorUser->id, $intern->user->id]);

            $this->seedTasks($space, $intern, $mentorUser, $team);
            $this->seedMessages($dm, $mentorUser, $intern->user);
        }

        $this->seedAnnouncement($announcement, $adminId);

        $this->command->info('Workspace: ' . Workspace::count() . ', tugas: ' . Task::count() . ', pesan: ' . Message::count());
    }

    private function teamWorkspace(Team $team, ?int $adminId): Workspace
    {
        return Workspace::firstOrCreate(
            ['type' => 'team', 'team_id' => $team->id],
            ['name' => 'Tim ' . $team->name, 'slug' => 'tim-' . $team->id . '-' . Str::slug($team->name), 'created_by' => $adminId]
        );
    }

    private function seedTasks(Workspace $space, Intern $intern, User $mentorUser, Team $team): void
    {
        if ($space->tasks()->exists()) {
            return;
        }

        $label = TaskLabel::firstOrCreate(['team_id' => $team->id, 'name' => 'dokumen'], ['color' => '#2563eb']);

        $plan = [
            [TaskStatus::Todo, TaskPriority::Medium, false],
            [TaskStatus::InProgress, TaskPriority::High, false],
            [TaskStatus::Review, TaskPriority::Medium, false],
            [TaskStatus::Revision, TaskPriority::Urgent, false],
            [TaskStatus::Done, TaskPriority::Low, false],
            [TaskStatus::InProgress, TaskPriority::High, true],
        ];

        foreach ($plan as $i => [$status, $priority, $overdue]) {
            DB::transaction(function () use ($space, $intern, $mentorUser, $label, $i, $status, $priority, $overdue) {
                $factory = Task::factory()->status($status);
                if ($overdue) {
                    $factory = $factory->overdue();
                }

                $task = $factory->create([
                    'workspace_id' => $space->id,
                    'number' => $i + 1,
                    'priority' => $priority,
                    'created_by' => $mentorUser->id,
                    'position' => $i,
                ]);

                $task->assignees()->attach($intern->id);
                $task->labels()->attach($label->id);
                $task->checklistItems()->createMany([
                    ['content' => 'Pelajari instruksi', 'is_done' => $status !== TaskStatus::Todo, 'position' => 0],
                    ['content' => 'Kerjakan dan kirim hasil', 'is_done' => $status === TaskStatus::Done, 'position' => 1],
                ]);

                TaskActivity::create([
                    'task_id' => $task->id,
                    'user_id' => $mentorUser->id,
                    'action' => 'created',
                    'meta' => ['status' => TaskStatus::Todo->value],
                ]);

                if ($status !== TaskStatus::Todo) {
                    TaskActivity::create([
                        'task_id' => $task->id,
                        'user_id' => $intern->user_id,
                        'action' => 'status_changed',
                        'meta' => ['from' => TaskStatus::Todo->value, 'to' => $status->value],
                    ]);
                }

                TaskComment::create([
                    'task_id' => $task->id,
                    'user_id' => $mentorUser->id,
                    'body' => 'Silakan dikerjakan, kabari kalau ada kendala.',
                ]);
            });
        }
    }

    private function seedMessages(Channel $dm, User $mentorUser, User $internUser): void
    {
        if ($dm->messages()->exists()) {
            return;
        }

        $root = Message::factory()->create([
            'channel_id' => $dm->id,
            'user_id' => $mentorUser->id,
            'body' => 'Selamat datang di Simagang, jangan sungkan bertanya di sini.',
        ]);

        Message::factory()->create([
            'channel_id' => $dm->id,
            'user_id' => $internUser->id,
            'parent_id' => $root->id,
            'body' => 'Terima kasih, Pak/Bu. Saya mulai dari tugas pertama.',
        ]);
    }

    private function seedAnnouncement(Channel $announcement, ?int $adminId): void
    {
        if ($announcement->messages()->exists() || ! $adminId) {
            return;
        }

        Message::factory()->pinned()->create([
            'channel_id' => $announcement->id,
            'user_id' => $adminId,
            'body' => 'Selamat datang di Workspace Simagang. Semua tugas dan diskusi kini lewat sini.',
        ]);
    }
}
