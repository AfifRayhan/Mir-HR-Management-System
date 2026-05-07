<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AttendanceService;
use Carbon\Carbon;

class ProcessAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:process {--date= : The date to process (YYYY-MM-DD), defaults to today}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process attendance logs for a specific date to generate attendance records';

    /**
     * Execute the console command.
     */
    public function handle(AttendanceService $attendanceService)
    {
        $dateStr = $this->option('date') ?: now()->toDateString();
        
        try {
            $date = Carbon::parse($dateStr);
            $this->info("Processing attendance logs for: " . $date->toDateString());
            
            $attendanceService->processLogsForDate($date->toDateString());
            
            $this->info("Successfully processed attendance for " . $date->toDateString());
        } catch (\Exception $e) {
            $this->error("Invalid date format or processing error: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
