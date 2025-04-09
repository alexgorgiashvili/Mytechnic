<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Automattic\WooCommerce\Client;
use Illuminate\Support\Facades\Log;

class SyncWooStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:sync-one';
    protected $description = 'Sync one product stock from WooCommerce';

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
        $product = Product::where(function ($query) {
            $query->whereNull('last_stock_sync_at')
                  ->orWhere('last_stock_sync_at', '<', now()->subHours(2));
        })
        ->has('stock') // ensure it has stock records
        ->orderBy('last_stock_sync_at')
        ->first();

        if (!$product) {
            $this->info("No product found for syncing.");
            return;
        }

        $this->info("Syncing product ID: {$product->id} - {$product->name}");

        $woo = new Client(
            env('WC_API_URL'),
            env('WC_CONSUMER_KEY'),
            env('WC_CONSUMER_SECRET'),
            ['version' => 'wc/v3', 'timeout' => 20,'verify_ssl' => false]
        );

        try {
            $wooProduct = $woo->get("products/{$product->barcode}");

            $totalStock = 0;

            if (!empty($wooProduct->variations)) {
                // Product has variants
                foreach ($product->stock as $stock) {
                    $wooVariant = $woo->get("products/{$product->barcode}/variations/{$stock->sku}");
                    $newQty = $wooVariant->stock_quantity ?? $stock->current_stock;

                    if ($newQty < 0) {
                        $newQty = 0; // Set to zero if negative
                    }

                    if ($stock->current_stock != $newQty) {
                        Log::info("Stock update: {$stock->id} | Old: {$stock->current_stock} | New: {$newQty}");
                        $stock->current_stock = $newQty;
                        $stock->save();
                    }

                    $totalStock += $newQty;
                }
            } else {
                // No variants, just update main stock
                $firstStock = $product->firstStock;
                $oldQty = $firstStock->current_stock;
                $newQty = $wooProduct->stock_quantity ?? 0;

                if ($newQty < 0) {
                    $newQty = 0; // Set to zero if negative
                }


                if ($oldQty != $newQty) {
                    Log::info("Stock update (no variants): {$firstStock->id} | Old: {$oldQty} | New: {$newQty}");
                    $firstStock->update(['current_stock' => $newQty]);
                } else {
                    Log::info("No stock change (no variants): {$firstStock->id} | Qty: {$oldQty}");
                }

                $totalStock = $newQty; // Set the total stock for the product
            }

            // Now update the total stock on the product record
            $product->current_stock = $totalStock;
            $product->last_stock_sync_at = now();
            $product->save();

            $this->info("Stock updated successfully for product {$product->id} with total stock: {$totalStock}");
        } catch (\Exception $e) {
            Log::error("Failed to sync stock for product {$product->id}: " . $e->getMessage());
            $this->error("Error: " . $e->getMessage());
        }
    }
}
