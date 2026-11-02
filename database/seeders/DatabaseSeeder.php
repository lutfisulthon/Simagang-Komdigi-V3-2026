<?php

namespace Database\Seeders;

use App\Models\Intern;
use App\Models\Mentor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $this->resetSeedableTables();

        $this->call([
            RoleAndPermissionSeeder::class,
        ]);

        /* super admin utama */
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@simagang.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password123'),
                'role' => 'super_admin',
            ]
        );
        $superAdmin->syncRoles(['super_admin']);

        $this->command->info('Super admin created');
        $this->command->info('Email: superadmin@simagang.com');
        $this->command->info('Password: password123');

        /* admin full access */
        $admin = User::updateOrCreate(
            ['email' => 'admin@simagang.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password123'),
                'role' => 'admin_full',
            ]
        );
        $admin->syncRoles(['admin_full']);

        $this->command->info('Admin full access created');
        $this->command->info('Email: admin@simagang.com');
        $this->command->info('Password: password123');

        User::where('email', 'admin2@simagang.com')->delete();


        /* reset data mentor*/
        // Intern::query()->update(['mentor_id' => null]);
        // User::where('role', 'mentor')->delete();
        // Mentor::query()->delete();


        /* seed mentor dan user */
        $mentorNames = [
            'Rudy Hermayadi',
            'Tasmil',
            'Herman',
            'Yayat Dendy Hadiyat',
            'Bahrawi',
            'Azwar Azis',
            'Olga Olivia Sombolayuk',
            'Nur Alam',
            'Farhan Rafsanjani',
            'Harbedy Hadya Tina',
            'Solehuddin Hasdin',
            'Muhammad Lutfi Sulthon Auliya S.',
            'Nur Fajriani Hipta',
            'Kalashnikov',
            'Fadly Aprianto',
            'Sukmawati',
            'Nuraeni Yuliati',
            'Rizqan Halalah MZ',
            'Muhammad Irfan',
            'Pierre Caesar Assyurah Tendean',
            'Muhammad Azham Subhan',
            'Idhil Cahyo Diputera',
            'Fairuz',
            'Muh. Andar Sugianto',
            'Rida Sulistiana Agustin',
            'Muhammad Harun Ashar',
            'Muhammad Agung',
        ];

        $mentors = collect($mentorNames)->map(function ($name) {

            $email = Str::slug($name, '.') . '@komdigi.com';

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password123'),
                    'role' => 'mentor',
                ]
            );
            $user->syncRoles(['mentor']);

            return Mentor::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'position' => null,
                    'phone' => null,
                    'is_active' => true,
                    'user_id' => $user->id,
                ]
            );
        });

        $this->command->info('Mentor users created: ' . $mentors->count());
        $this->command->info('Default mentor password: password123');


        /* intern user */
        $interns = [
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad.fauzi@example.com',
                'gender' => 'Laki-laki',
                'education_level' => 'S1/D4',
                'major' => 'Teknik Informatika',
                'phone' => '081234567890',
                'institution' => 'Universitas Contoh',
                'start_date' => now()->subDays(30),
                'end_date' => now()->addDays(60),
            ],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@example.com',
                'gender' => 'Perempuan',
                'education_level' => 'SMA/SMK',
                'major' => 'Rekayasa Perangkat Lunak',
                'phone' => '089876543210',
                'institution' => 'SMK Teknologi',
                'start_date' => now()->subDays(20),
                'end_date' => now()->addDays(70),
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@example.com',
                'gender' => 'Laki-laki',
                'education_level' => 'S1/D4',
                'major' => 'Sistem Informasi',
                'phone' => '081122334455',
                'institution' => 'Universitas Negeri',
                'start_date' => now()->subDays(10),
                'end_date' => now()->addDays(80),
            ],
        ];

        foreach ($interns as $index => $internData) {

            $user = User::updateOrCreate(
                ['email' => $internData['email']],
                [
                    'name' => $internData['name'],
                    'password' => Hash::make('password123'),
                    'role' => 'intern',
                ]
            );
            $user->syncRoles(['intern']);

            $mentor = $mentors->count() > 0
                ? $mentors[$index % $mentors->count()]
                : null;

            Intern::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $internData['name'],
                    'gender' => $internData['gender'],
                    'education_level' => $internData['education_level'],
                    'major' => $internData['major'],
                    'phone' => $internData['phone'],
                    'institution' => $internData['institution'],
                    'mentor_id' => $mentor?->id,
                    'start_date' => $internData['start_date'],
                    'end_date' => $internData['end_date'],
                    'photo_path' => null,
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('Sample intern users created');
        $this->command->info('Default intern password: password123');

        $this->call([
            TeamSeeder::class,
            TestimonialSeeder::class,
            MicroSkillsSeeder::class,
        ]);
    }

    private function resetSeedableTables(): void
    {
        $tables = [
            'model_has_permissions',
            'model_has_roles',
            'role_has_permissions',
            'permissions',
            'roles',
            'testimonials',
            'final_reports',
            'certificate_scores',
            'certificates',
            'pengajuan_details',
            'pengajuans',
            'micro_skill_submissions',
            'logbooks',
            'attendances',
            'institusis',
            'interns',
            'mentors',
            'teams',
            'users',
        ];

        Schema::disableForeignKeyConstraints();

        try {
            foreach ($tables as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                }
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
}