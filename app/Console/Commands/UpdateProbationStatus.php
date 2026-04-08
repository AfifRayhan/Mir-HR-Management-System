<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateProbationStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-probation-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update employee type from Probation to Regular for employees whose probation has ended';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        \App\Models\Employee::probationEnded()->update([
            'employee_type' => 'Regular'
        ]);

        $this->info('Probation status updated successfully.');
    }
}
