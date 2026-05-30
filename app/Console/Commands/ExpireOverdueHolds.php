<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Hold;
use Illuminate\Support\Facades\DB;
use App\Enums\HoldStatus;
use App\Events\HoldExpired;

#[Signature('app:expire-overdue-holds')]
#[Description('Expire holds that have passed their expiration time.')]
class ExpireOverdueHolds extends Command
{
    public function handle(): int 
    {
        $overdue = Hold::query()->overdue()->get();

        if ($overdue->isEmpty()) {
            $this->info('No overdue holds found.');
            
            return self::SUCCESS;
        }

        DB::transaction(function () use ($overdue) {
            Hold::query()
                ->whereIn('id', $overdue->pluck('id'))
                ->update(['status' => HoldStatus::Expired->value]);
        });

        foreach ($overdue as $hold) {
            HoldExpired::dispatch($hold->fresh());
        }

        $count = $overdue->count();
        $this->info("Expired {$count} hold(s).");

        return self::SUCCESS;
    }
}
