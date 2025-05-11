<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Lista de comenzi Artisan pentru aplicație.
     *
     * @var array
     */
    protected $commands = [
        Commands\ProcessIdocMessages::class,
    ];

    /**
     * Definește programul de comenzi pentru aplicație.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Procesăm IDOC-urile la fiecare minut
        $schedule->command('idoc:process')
            ->everyMinute()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/idoc-process.log'));

        // Curățăm mesajele IDOC procesate mai vechi de 30 de zile
        $schedule->command('idoc:cleanup --days=30')
            ->daily()
            ->appendOutputTo(storage_path('logs/idoc-cleanup.log'));

        // Curățăm job-urile eșuate mai vechi de 7 zile
        $schedule->command('queue:prune-failed --hours=168')
            ->daily();
    }

    /**
     * Înregistrează comenzile pentru aplicație.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
} 