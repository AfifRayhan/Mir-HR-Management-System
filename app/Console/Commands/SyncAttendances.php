<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Attendance;
use Carbon\Carbon;

class SyncAttendances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-attendances {--sync-only : Only sync data without processing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync today\'s attendance data from external db and auto-process logs';

    /**
     * Execute the console command.
     */
    public function handle(\App\Services\AttendanceService $attendanceService)
    {
        $this->info('Starting attendance sync...');

        try {
            DB::connection('attendance_analysis')
                ->table('device_attendances')
                ->where('created_at', '>=', now()->startOfDay()) // Sync today's data only
                ->chunkById(1000, function ($records) {
                    $insertData = [];
                    foreach ($records as $record) {
                        $insertData[] = [
                            'user_id' => $record->user_id,
                            'punch_time' => $record->punch_time,
                            'status' => $record->status,
                            'device_name' => $record->device_name,
                            'machine_id' => $record->machine_id,
                            'created_at' => $record->created_at ?? now(),
                            'updated_at' => now(),
                        ];
                    }

                    if (count($insertData) > 0) {
                        Attendance::upsert(
                            $insertData,
                            ['user_id', 'punch_time', 'machine_id'],
                            ['status', 'device_name', 'updated_at', 'created_at']
                        );
                    }
                });

            $this->info('Attendance sync completed successfully.');

            if (!$this->option('sync-only')) {
                $date = now()->toDateString();
                $this->info("Starting attendance log processing for $date...");
                $attendanceService->processLogsForDate($date);
                $this->info("Attendance processing for $date completed successfully.");
            }
        } catch (\Exception $e) {
            $this->error('Sync failed: ' . $e->getMessage());
        }
    }
}
