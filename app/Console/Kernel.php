<?php

namespace App\Console;

use App\Jobs\CheckPixPayment;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            $pendingSales = \App\Models\Sale::where('status', 'pending')
                                            ->whereNotNull('pix_txid')
                                            ->get();

            $pixRepository = app(\App\Repositories\Sale\PixRepository::class);

            foreach ($pendingSales as $sale) {
                (new \App\Jobs\CheckPixPayment($sale->pix_txid, $pixRepository))->handle();
            }
        })->everyTenSeconds();

    }


    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
