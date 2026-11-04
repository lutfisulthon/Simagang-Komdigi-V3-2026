<?php

namespace Database\Seeders;

use App\Models\Intern;
use App\Models\Mentor;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Tiga anak bimbingan untuk mentor Muhammad Lutfi Sulthon Auliya S. (tim FGA).
 * Idempoten: aman dijalankan ulang. Workspace (tim + Space) dibuat otomatis oleh provisioner.
 */
class MentorLutfiInternSeeder extends Seeder
{
    private const MENTOR_EMAIL = 'muhammad.lutfi.sulthon.auliya.s@komdigi.com';

    public function run(): void
    {
        $mentor = Mentor::where('email', self::MENTOR_EMAIL)->first();
        $team = Team::where('name', 'FGA')->first();

        if (! $mentor || ! $team) {
            $this->command->warn('Mentor Lutfi atau tim FGA belum ada, MentorLutfiInternSeeder dilewati.');
            return;
        }

        if (! $mentor->team_id) {
            $mentor->update(['team_id' => $team->id]);
        }

        $interns = [
            ['name' => 'Rizky Pratama', 'email' => 'rizky.pratama@example.com', 'gender' => 'Laki-laki', 'education_level' => 'S1/D4', 'major' => 'Teknik Informatika', 'phone' => '081234500011', 'institution' => 'Universitas Hasanuddin'],
            ['name' => 'Nadia Safitri', 'email' => 'nadia.safitri@example.com', 'gender' => 'Perempuan', 'education_level' => 'S1/D4', 'major' => 'Sistem Informasi', 'phone' => '081234500012', 'institution' => 'Universitas Negeri Makassar'],
            ['name' => 'Fajar Ramadhan', 'email' => 'fajar.ramadhan@example.com', 'gender' => 'Laki-laki', 'education_level' => 'SMA/SMK', 'major' => 'Rekayasa Perangkat Lunak', 'phone' => '081234500013', 'institution' => 'SMK Negeri 2 Makassar'],
        ];

        foreach ($interns as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name'], 'password' => Hash::make('password123'), 'role' => 'intern']
            );
            $user->syncRoles(['intern']);

            Intern::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $data['name'],
                    'gender' => $data['gender'],
                    'education_level' => $data['education_level'],
                    'major' => $data['major'],
                    'phone' => $data['phone'],
                    'institution' => $data['institution'],
                    'mentor_id' => $mentor->id,
                    'team_id' => $team->id,
                    'start_date' => now()->subDays(14),
                    'end_date' => now()->addDays(76),
                    'photo_path' => null,
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('3 anak bimbingan mentor Lutfi dibuat (password: password123)');
    }
}
