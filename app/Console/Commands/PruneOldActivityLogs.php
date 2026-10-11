<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PruneOldActivityLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:prune {--days=3 : Jumlah hari retensi log sebelum dihapus}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis membersihkan log aktivitas sistem yang berumur lebih dari 3 hari';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days') ?: 3;
        $threshold = Carbon::now()->subDays($days);

        $deletedCount = ActivityLog::where('created_at', '<', $threshold)->delete();

        $this->info("Berhasil mereset log aktivitas yang berusia lebih dari {$days} hari. Total log dihapus: {$deletedCount}.");

        return Command::SUCCESS;
    }
}
