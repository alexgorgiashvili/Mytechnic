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
        ->has('stock')
        ->orderBy('last_stock_sync_at')
        ->first();
    
        if (!$product) {
            $this->info("No product found for syncing.");
            Log::info("No product found for syncing.");
            return;
        }
    
        $this->info("Syncing product ID: {$product->id} - {$product->name}");
        Log::info("Syncing product ID: {$product->id} - {$product->name}");
    
        $woo = new Client(
            env('WC_API_URL'),
            env('WC_CONSUMER_KEY'),
            env('WC_CONSUMER_SECRET'),
            ['version' => 'wc/v3', 'timeout' => 20, 'verify_ssl' => false]
        );
    
        try {
            $wooProduct = $woo->get("products/{$product->barcode}");
    
            $totalStock = 0;
    
            if (!empty($wooProduct->variations)) {
                Log::info("Product {$product->id} has variants. Fetching all variant stock at once.");
    
                // Fetch all variations at once
                $wooVariants = $woo->get("products/{$product->barcode}/variations?per_page=100");
    
                // Index Woo variants by SKU
                $wooVariantsById = [];
                foreach ($wooVariants as $variant) {
                    $wooVariantsById[$variant->id] = $variant;
                }
                
    
                foreach ($product->stock as $stock) {
                    $wooVariant = $wooVariantsById[$stock->sku] ?? null;
    
                    if (!$wooVariant) {
                        Log::warning("WooCommerce variant not found for SKU: {$stock->sku} (Product ID: {$product->id})");
                        continue;
                    }
    
                    $newQty = $wooVariant->stock_quantity ?? $stock->current_stock;
    
                    Log::info("Before Sync (Variant): {$stock->id} | SKU: {$stock->sku} | Old: {$stock->current_stock} | Woo: {$newQty}");
    
                    if ($newQty < 0) {
                        $newQty = 0;
                    }
    
                    if ($stock->current_stock != $newQty) {
                        Log::info("Updating Stock (Variant): {$stock->id} | SKU: {$stock->sku} | New Qty: {$newQty}");
                        $stock->current_stock = $newQty;
                        $stock->save();
                    } else {
                        Log::info("No stock change (Variant): {$stock->id} | SKU: {$stock->sku}");
                    }
    
                    $totalStock += $newQty;
                }
            } else {
                Log::info("Product {$product->id} has no variants. Updating main stock.");
    
                $firstStock = $product->firstStock;
                $oldQty = $firstStock->current_stock;
                $newQty = $wooProduct->stock_quantity ?? 0;
    
                Log::info("Before Sync (No Variant): {$firstStock->id} | Old: {$oldQty} | Woo: {$newQty}");
    
                if ($newQty < 0) {
                    $newQty = 0;
                }
    
                if ($oldQty != $newQty) {
                    Log::info("Updating Stock (No Variant): {$firstStock->id} | New Qty: {$newQty}");
                    $firstStock->update(['current_stock' => $newQty]);
                } else {
                    Log::info("No stock change (No Variant): {$firstStock->id} | Qty: {$oldQty}");
                }
    
                $totalStock = $newQty;
            }
    
            $product->current_stock = $totalStock;
            $product->last_stock_sync_at = now();
            $product->save();
    
            Log::info("Final Stock Sync: Product {$product->id} | Total Stock: {$totalStock}");
            $this->info("Stock updated successfully for product {$product->id} with total stock: {$totalStock}");
    
        } catch (\Exception $e) {
            Log::error("Failed to sync stock for product {$product->id}: " . $e->getMessage());
            Log::error("Stack Trace: " . $e->getTraceAsString());
            $this->error("Error syncing product {$product->id}: " . $e->getMessage());
        }
    }
    
    
}
