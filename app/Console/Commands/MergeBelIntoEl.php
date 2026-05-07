<?php
// app/Console/Commands/MergeBelIntoEl.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use Illuminate\Support\Facades\DB;

class MergeBelIntoEl extends Command
{
    protected $signature   = 'leave:merge-bel-into-el';
    protected $description = 'One-time: merge all Bonus Earn Leave (BEL) balances into Earn Leave (EL) and remove BEL type.';

    public function handle(): int
    {
        $bel = LeaveType::where('name', 'LIKE', '%Bonus%')
                        ->where('name', 'LIKE', '%Earn%')
                        ->first();

        if (!$bel) {
            $this->info('No Bonus Earn Leave type found — nothing to migrate.');
            return self::SUCCESS;
        }

        $el = LeaveType::where('name', 'LIKE', '%Earn Leave%')
                       ->where('name', 'NOT LIKE', '%Bonus%')
                       ->first();

        if (!$el) {
            $this->error('Earn Leave (EL) type not found. Aborting.');
            return self::FAILURE;
        }

        $belBalances = LeaveBalance::where('leave_type_id', $bel->id)->get();
        $merged = 0;

        DB::transaction(function () use ($belBalances, $el, $bel, &$merged) {
            foreach ($belBalances as $belBalance) {
                $elBalance = LeaveBalance::where('employee_id', $belBalance->employee_id)
                    ->where('leave_type_id', $el->id)
                    ->where('year', $belBalance->year)
                    ->first();

                if ($elBalance) {
                    // Merge BEL into existing EL row, capped at 40
                    $elBalance->opening_balance = min(40, $elBalance->opening_balance + $belBalance->opening_balance);
                    $elBalance->used_days       += $belBalance->used_days;
                    $elBalance->remaining_days  = max(0, $elBalance->opening_balance - $elBalance->used_days);
                    $elBalance->save();
                } else {
                    // No EL row exists — create one from BEL's values under EL type (capped at 40)
                    $opening = min(40, $belBalance->opening_balance);
                    $used = $belBalance->used_days;
                    LeaveBalance::create([
                        'employee_id'    => $belBalance->employee_id,
                        'leave_type_id'  => $el->id,
                        'year'           => $belBalance->year,
                        'opening_balance'=> $opening,
                        'used_days'      => $used,
                        'remaining_days' => max(0, $opening - $used),
                    ]);
                }

                $belBalance->delete();
                $merged++;
            }

            // Delete the BEL leave_type record itself
            $bel->delete();
        });

        $this->info("Done. {$merged} BEL balance row(s) merged into EL. BEL leave type deleted.");
        return self::SUCCESS;
    }
}
