<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DbBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Database Backup';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            Log::info("Database backup Start.");

            $date = Carbon::now()->format('Y-m-d_H-i-s');
            $databaseName = env('DB_DATABASE');
            $backupFolder = storage_path("app/backups");
            $backupFile = storage_path("app/backups/{$databaseName}_{$date}.sql");

            if (!file_exists($backupFolder)) {
                mkdir($backupFolder, 0777, true);
            }

            $command = "mysqldump --user=" . env('DB_USERNAME') . " --password=" . env('DB_PASSWORD') . " --host=" . env('DB_HOST') . " {$databaseName} > {$backupFile}";

            $status = shell_exec($command);

            if ($status === null) {
                Log::info("Database backup completed successfully.");
                $this->deleteOldBackups($backupFolder, 3);
                $this->info("Database backup completed successfully.");
            } else {
                Log::error("Error during backup process.");
                $this->error("Error during backup process.");
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
        }
    }

    private function deleteOldBackups($backupFolder, $keep = 3)
    {
        // ดึงรายการไฟล์ทั้งหมดในโฟลเดอร์ backup และเรียงตามวันที่จากใหม่ -> เก่า
        $files = collect(scandir($backupFolder))
            ->filter(fn($file) => str_ends_with($file, '.sql')) // คัดเอาเฉพาะไฟล์ .sql
            ->sortDesc()
            ->values();

        if ($files->count() > $keep) {
            $filesToDelete = $files->slice($keep);
            foreach ($filesToDelete as $file) {
                unlink("{$backupFolder}/{$file}");
                Log::info("Deleted old backup: {$file}");
                $this->info("Deleted old backup: {$file}");

            }
        }
    }
}
