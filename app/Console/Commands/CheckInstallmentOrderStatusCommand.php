<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Jobs\CheckInstallmentOrderStatus;
use App\Services\CredoInstallmentService;


class CheckInstallmentOrderStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'installments:check';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the installment order status.';

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
        Log::channel('mylog')->info('CheckInstallmentOrderStatusCommand is starting.');
        
        $credoInstallmentService = app(CredoInstallmentService::class);
        CheckInstallmentOrderStatus::dispatch($credoInstallmentService);
        
        $this->info('CheckInstallmentOrderStatus job dispatched.');
        Log::channel('mylog')->info('CheckInstallmentOrderStatus job dispatched successfully.');
    }
    
}
