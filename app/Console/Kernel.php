<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
   
    protected function schedule(Schedule $schedule): void
    {
   
        $schedule->command('attendance:mark-alfa')
            ->dailyAt('12:05')
            ->timezone('Asia/Makassar');

        $schedule->command('workspace:check-deadlines')
            ->hourly()
            ->timezone('Asia/Makassar');

        $schedule->command('workspace:notify-unread-chat')
            ->everyFifteenMinutes()
            ->timezone('Asia/Makassar');

        $schedule->command('workspace:archive-expired')
            ->dailyAt('00:10')
            ->timezone('Asia/Makassar');
    }


    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
