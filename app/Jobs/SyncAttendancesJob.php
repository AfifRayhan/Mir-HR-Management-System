<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncAttendancesJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(\App\Services\AttendanceService $attendanceService): void
    {
        Log::info('SyncAttendancesJob: Starting attendance sync...');
        try {
            $count = 0;
            DB::connection('attendance_analysis')
                ->table('device_attendances')
                ->where('created_at', '>=', now()->startOfDay()) // Sync today's data only
                ->chunkById(1000, function ($records) use (&$count) {
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
                        $count += count($insertData);
                    }
                });

            Log::info("SyncAttendancesJob: Synced $count records from external DB.");

            $date = now()->toDateString();
            Log::info("SyncAttendancesJob: Processing logs for date: $date");
            $attendanceService->processLogsForDate($date);
            Log::info('SyncAttendancesJob: Completed successfully.');
            
        } catch (\Exception $e) {
            Log::error('SyncAttendancesJob FAILED: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }
}
