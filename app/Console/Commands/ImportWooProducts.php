<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ImportWooProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'woo:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import products from local WooCommerce JSON export';

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
        $controller = app(\App\Http\Controllers\WooCommerceController::class);
        $count = $controller->fetchSave();
    
        $this->info("✅ WooCommerce import complete. Imported {$count} new products.");
    
    
    }
}
