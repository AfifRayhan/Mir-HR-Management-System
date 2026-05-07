<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RosterTime;

class FixOvernightShifts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:fix-overnight-shifts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set is_overnight to true for all roster shifts that cross midnight';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Identifying shifts that cross midnight...');

        $shifts = RosterTime::whereNotNull('start_time')
            ->whereNotNull('end_time')
            ->whereColumn('start_time', '>', 'end_time')
            ->where(function($q) {
                $q->where('is_overnight', false)
                  ->orWhereNull('is_overnight');
            })
            ->get();

        if ($shifts->isEmpty()) {
            $this->info('No misconfigured shifts found.');
            return;
        }

        foreach ($shifts as $shift) {
            $this->line("Fixing: [{$shift->group_slug}] {$shift->shift_key} ({$shift->start_time} - {$shift->end_time})");
            $shift->update(['is_overnight' => true]);
        }

        $this->info('Successfully fixed ' . $shifts->count() . ' shifts.');
    }
}
