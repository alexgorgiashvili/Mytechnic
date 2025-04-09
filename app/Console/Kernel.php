<?php

namespace App\Console;

use App\Console\Commands\AllClear;
use Illuminate\Support\Facades\Log;
use App\Console\Commands\SyncWooStock;
use App\Jobs\CheckInstallmentOrderStatus;
use App\Services\CredoInstallmentService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        AllClear::class,
        SyncWooStock::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // Log the start of the scheduling process    

        // $schedule->command('subscription:check')->when(function () {
        //     return addon_is_activated('seller_subscription') && settingHelper('seller_system') == 1;
        // })->everyFifteenMinutes();
        
        // Schedule the job for checking installment order status
        // $schedule->command('installments:check')->everyMinutes();

        $schedule->command('stock:sync-one')->everyMinute();



        
        
    }
    
    

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}