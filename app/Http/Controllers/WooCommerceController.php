<?php

namespace App\Http\Controllers;

use OpenAI;
use App\Models\Color;
use App\Models\Product;
use App\Traits\SlugTrait;
use App\Traits\ImageTrait;
use Illuminate\Support\Str;
use App\Models\ProductStock;
use Illuminate\Http\Request;
use App\Models\ColorLanguage;
use App\Models\AttributeValues;
use App\Models\ProductLanguage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\WooCommerceService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Repositories\Interfaces\Admin\MediaInterface;
use App\Repositories\Admin\Product\ProductLanguageRepository;

class WooCommerceController extends Controller
{
    use SlugTrait,ImageTrait;

    
    protected $wooCommerceService;
    protected $medias;

    public function __construct(WooCommerceService $wooCommerceService, MediaInterface $medias,)
    {
        $this->wooCommerceService = $wooCommerceService;
        $this->medias = $medias;


    }


    // public function previewSimpleProductStockMismatches()
    // {
    //     $jsonPath = storage_path('app/woocommerce_products.json');
    //     $jsonData = json_decode(File::get($jsonPath), true);
        
    //     // Extract just the WooCommerce IDs from JSON (for matching)
    //     $wooProductIds = collect($jsonData)
    //         ->filter(fn($item) => empty($item['variations']))
    //         ->pluck('id')
    //         ->toArray();
        
    //     // Get matching products WITH THEIR STOCK AND PRICE from your database
    //     $products = Product::whereIn('barcode', $wooProductIds)
    //         ->get(['id', 'barcode', 'current_stock', 'price']); // Include all fields you need
        
    //     // Prepare productstocks data using YOUR PRODUCT DATA
    //     $stockRecords = $products->map(function ($product) {
    //         return [
    //             'product_id' => $product->id,
    //             'sku' => $product->barcode,
    //             'current_stock' => $product->current_stock, // From your products table
    //             'price' => $product->price, // From your products table
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //             // Add any other fields you need from products table
    //         ];
    //     })->toArray();
        
    //     // Batch insert
    //     if (!empty($stockRecords)) {
    //         DB::table('product_stocks')->insert($stockRecords);
    //     }
        
    //     // Output results
    //     $createdCount = count($stockRecords);
    //     echo "Successfully created {$createdCount} product stock records using local product data";
    // }



    public function testing()
    {
        // $prod = $this->wooCommerceService->getProductById(18380);
        // $search = '6 თვის სრული დაფარვა';
        // $replace = '6 თვე';
        // $warrantyProducts = ProductLanguage::where('description', 'LIKE', '%6 თვის%')
        // ->get();
        // ProductLanguage::where('description', 'LIKE', '%'.$search.'%')
        // ->update([
        //     'description' => DB::raw("REPLACE(description, '$search', '$replace')")
        // ]);       
        $jsonPath = storage_path('app/woocommerce_products.json');
        $jsonData = json_decode(File::get($jsonPath), true);
        $response = collect($jsonData)->firstWhere('name', 'Apple iPad 5th Generation');
        // $prod = Product::where('has_variant',0)->whereJsonLength('colors', '>', 0)->update(['has_variant' => 1]);
        // $affectedRows = Product::where('selected_variants', '{"1":[]}')
        // ->update([
        //     'selected_variants' => [],
        //     'attribute_sets' => []
        // ]);
    
    // Output the number of updated records
    
    dd($response);
        // Load local JSON


        die;
        $jsonSimpleCount = collect($jsonData)
            ->filter(fn($item) => empty($item['variations']))
            ->count();

        // 🔹 Count from DB (has_variant = 0)
        $dbSimpleCount = \App\Models\Product::where('has_variant', 0)->count();

        Log::info("📄 JSON simple products (no variations): {$jsonSimpleCount}");
        Log::info("🗂️ DB simple products (has_variant = 0): {$dbSimpleCount}");

        return [
            'json_simple_products' => $jsonSimpleCount,
            'db_simple_products' => $dbSimpleCount,
        ];

    
    }

    // public function testing()
    // {
    //     // $prod = $this->wooCommerceService->getProductById(18380);
    //     // dd($prod);
    //     $jsonPath = storage_path('app/woocommerce_products.json');
    //     $jsonData = json_decode(File::get($jsonPath), true);
    
    //     if (!is_array($jsonData)) {
    //         Log::error("❌ Invalid JSON format.");
    //         return 0;
    //     }
    
    //     $jsonProducts = collect($jsonData)->keyBy('id');
    //     $updatedCount = 0;
    
    //     // ✅ Get only simple products (no variants) from DB
    //     $products =Product::where('has_variant', 0)->get();

    //     dd($products);
    
    //     foreach ($products as $product) {
    //         $wooId = (int) $product->barcode;
    //         if (!$jsonProducts->has($wooId)) continue;
    
    //         $jsonItem = $jsonProducts[$wooId];
    //         $jsonStock = isset($jsonItem['stock_quantity']) ? max(0, (int) $jsonItem['stock_quantity']) : 0;
    
    //         if ($product->current_stock !== $jsonStock) {
    //             Log::info("🔁 Updating stock for '{$product->name}' (ID {$product->id}) from {$product->current_stock} → {$jsonStock}");
    
    //             $product->current_stock = $jsonStock;
    //             $product->stock_visibility = $jsonStock > 0 ? 'visible_with_text' : 'hide_stock';
    //             $product->save();
    
    //             $updatedCount++;
    //         }
    //     }
    
    //     Log::info("✅ Stock sync complete. Updated {$updatedCount} simple products.");
    //     return $updatedCount;


    // }
    


    // public function fetchAndSaveProducts()
    // {
    //     ini_set('max_execution_time', 0); // No time limit
    //     ini_set('memory_limit', '1G');

    //     $page = 1;
    //     $perPage = 50;
    //     $allProducts = [];
    
    //     do {
    //         // Fetch main products
    //         $products = $this->wooCommerceService->getAllProducts($perPage, $page);
    
    //         if (!is_array($products) || empty($products)) {
    //             Log::info("No more products found on page $page.");
    //             break;
    //         }
    
    //         // Fetch variations for variable products
    //         foreach ($products as &$product) {
    //             if ($product['type'] === 'variable') { // Check if product has variations
    //                 $variations = $this->wooCommerceService->getProductVariations($product['id']);
    
    //                 if (!empty($variations)) {
    //                     $product['variations'] = $variations; // Attach variations to product
    //                     Log::info("Fetched " . count($variations) . " variations for product ID " . $product['id']);
    //                 }
    //             }
    //         }
    
    //         $allProducts = array_merge($allProducts, $products);
    //         Log::info("Fetched Page $page - " . count($products) . " products.");
    
    //         $page++;
    //         sleep(1); // Prevent API rate limits
    
    //     } while (count($products) === $perPage);
    
    //     // Save data to JSON file
    //     Storage::put('woocommerce_products.json', json_encode($allProducts, JSON_PRETTY_PRINT));
    
    //     return response()->json(['message' => "Saved WooCommerce data to JSON file.", 'total' => count($allProducts)]);
    // }
    


// function getMissingProducts()
// {

//     $prod = Product::where('barcode','SKU-17590')->first();
//     dd($prod);
//     // Load the WooCommerce JSON file
//     $jsonFilePath = storage_path('app/woocommerce_products.json');

//     if (!file_exists($jsonFilePath)) {
//         return ['error' => 'WooCommerce JSON file not found.'];
//     }

//     // Read and decode JSON
//     $jsonData = file_get_contents($jsonFilePath);
//     $products = json_decode($jsonData, true);

//     if (!$products || !is_array($products)) {
//         return ['error' => 'Invalid WooCommerce JSON data.'];
//     }

//     // Get SKUs from WooCommerce JSON
//     $woocommerceSkus = array_column($products, 'sku');

//     // Get SKUs from the database
//     $dbSkus = DB::table('products')->pluck('barcode')->toArray();

//     // Find missing SKUs (SKUs that exist in WooCommerce but not in DB)
//     $missingSkus = array_diff($woocommerceSkus, $dbSkus);

//     // Get full product details of missing SKUs
//     $missingProducts = array_filter($products, function ($product) use ($missingSkus) {
//         return in_array($product['sku'], $missingSkus);
//     });

//     // Save missing products to a file for reference
//     Storage::put('woocommerce_missing_products.json', json_encode(array_values($missingProducts), JSON_PRETTY_PRINT));

//     return [
//         'total_missing' => count($missingProducts),
//         'missing_skus' => array_values($missingSkus),
//         'missing_products' => array_values($missingProducts),
//         'saved_file' => 'storage/app/woocommerce_missing_products.json'
//     ];
// }

// public function saveMissingProductsByWooId()
// {
//     $jsonPath = storage_path('app/woocommerce_products.json');
//     $jsonData = json_decode(File::get($jsonPath), true);

//     if (!is_array($jsonData) || empty($jsonData)) {
//         Log::error("❌ Invalid or empty JSON file.");
//         return;
//     }

//     $jsonProducts = collect($jsonData)->keyBy('id');
//     $existingBarcodes = \App\Models\Product::pluck('barcode')->toArray(); // existing Woo IDs
//     $missingProducts = $jsonProducts
//     ->except($existingBarcodes)
//     ->except([30668]); 

//     // dd($missingProducts);
//     if ($missingProducts->isEmpty()) {
//         Log::info("✅ No missing products found.");
//         return;
//     }

//     $saved = 0;

//     foreach ($missingProducts as $wooProductId => $productData) {
//         try {
//             Log::info("📥 Saving missing product ID: {$wooProductId} — {$productData['name']}");

//             $product = $this->saveBasicProductDetails($productData);
//             if ($product) {
//                 $this->wooCommerceService->storeProductLanguage($product->id, $productData);

//                 if (!empty($productData['variations'])) {
//                     $this->storeProductVariants($product, $productData['variations']);
//                 }

//                 $saved++;
//             }
//         } catch (\Throwable $e) {
//             Log::error("❌ Error saving product ID {$wooProductId}: " . $e->getMessage());
//         }
//     }

//     Log::info("✅ Finished importing missing products. Total saved: {$saved}");
//     return $saved;
// }




public function fetchSave()
{
    // Load JSON data
    $jsonPath = storage_path('app/woocommerce_products.json');
    $jsonData = json_decode(File::get($jsonPath), true);

    // Get product from JSON by ID
    // $response = collect($jsonData)->firstWhere('id', 18193);
    $response = collect($jsonData)->firstWhere('slug', 'magnetic-clear-case-for-samsung-galaxy-s23-ultra');

    dd($response);
    if (!is_array($response) || isset($response['error'])) {
        Log::error("WooCommerce API request failed");
        return response()->json(['error' => 'Failed to fetch product'], 500);
    }

    Log::info("Fetched product: " . $response['name']);

    // Save product details
    $product = $this->saveBasicProductDetails($response);
    // dd($product);

    if ($product) {
        // ✅ Store Product Language Details
        $this->wooCommerceService->storeProductLanguage($product->id, $response);

        // ✅ Store product variants
        if (!empty($response['variations'])) {
            Log::info("Processing variations for product: " . $response['name']);
            $this->storeProductVariants($product, $response['variations']);
        }
    }

    return response()->json(['message' => 'Product imported successfully.']);
}

// public function fetchSave()
// {
//     $jsonPath = storage_path('app/woocommerce_products.json');
//     $jsonData = json_decode(File::get($jsonPath), true);

//     if (!is_array($jsonData) || empty($jsonData)) {
//         Log::error("❌ WooCommerce product data is empty or invalid.");
//         return 0;
//     }

//     $imported = 0;

//     // 🔁 Chunk the product list (e.g. 10 per batch)
//     $chunks = array_chunk($jsonData, 10);

//     foreach ($chunks as $chunk) {
//         foreach ($chunk as $response) {
//             try {
//                 $sku = $response['sku'] ?? null;

//                 if (!$sku) {
//                     Log::warning("⏭️ Skipping product without SKU: " . ($response['name'] ?? 'Unnamed'));
//                     continue;
//                 }

//                 $existing = Product::where('barcode', $sku)->first();
//                 if ($existing) {
//                     Log::info("🔁 Skipping existing product with SKU: {$sku}");
//                     continue;
//                 }

//                 $product = $this->saveBasicProductDetails($response);

//                 if ($product) {
//                     $this->wooCommerceService->storeProductLanguage($product->id, $response);

//                     if (!empty($response['variations'])) {
//                         Log::info("📦 Saving variants for: {$response['name']}");
//                         $this->storeProductVariants($product, $response['variations']);
//                     }

//                     $imported++;
//                     Log::info("✅ Imported product SKU: {$sku}");
//                 }
//             } catch (\Throwable $e) {
//                 Log::error("❌ Failed to import product: " . ($response['name'] ?? 'Unknown') . " — " . $e->getMessage());
//             }
//         }

//         // 💤 Optional: prevent rate-limiting or give CPU a break
//         sleep(1);
//     }

//     Log::info("✅ Imported {$imported} products total.");
//     return $imported;
// }








private function saveBasicProductDetails(array $apiProduct)
{
    $slug = $this->getSlug($apiProduct['name'], $apiProduct['slug']);

    // Load category and brand mappings
    $mappings = config('mappings');


    $categoryId = $mappings['categories'][$apiProduct['categories'][0]['id']] ?? 11;
    
    $brandName = collect($apiProduct['attributes'])
        ->firstWhere('slug', 'pa_brand')['options'][0] ?? null;

    $brandId = $mappings['brands'][$brandName] ?? 1;

    // Check if product already exists
    $product = Product::where('slug', $slug)->first() ?? new Product();

    // Set default values
    $price = floatval($apiProduct['price'] ?? 0);
    $specialDiscount = !empty($apiProduct['sale_price']) ? floatval($apiProduct['sale_price']) : null;
    $totalStock = 0;

    if (!empty($apiProduct['variations'])) {
        foreach ($apiProduct['variations'] as $variant) {
            if (isset($variant['stock_quantity']) && is_numeric($variant['stock_quantity'])) {
                $stock = max(0, (int) $variant['stock_quantity']); 
                $totalStock += $stock;
            }
        }
    } else {
        // ✅ Fallback: Use main product's stock_quantity if no variations
        if (isset($apiProduct['stock_quantity']) && is_numeric($apiProduct['stock_quantity'])) {
            $totalStock = max(0, (int) $apiProduct['stock_quantity']);
        }
    }
    
    
    // ✅ Set Stock Visibility Based on Variants
    $stockVisibility = ($totalStock > 0) ? 'visible_with_text' : 'hide_stock';
    $barcode = $apiProduct['id'] ?? null;

    // ✅ Extract Colors and Memory Variant IDs
    $colorIds = [];
    $selectedVariants = ["1" => []]; // Format: {"1":["2","3"]}
    $selectedVariantIds = [];


    if (!empty($apiProduct['attributes'])) {
        foreach ($apiProduct['attributes'] as $attribute) {
            if (!empty($attribute['options'])) {
                foreach ($attribute['options'] as $value) {
                    $value = trim($value);

                    // Handle Colors
                    if (strtolower($attribute['slug']) === 'pa_color') {
                        if ($colorId = ColorLanguage::where('name', $value)->value('color_id')) {
                            $colorIds[] = (string) $colorId;
                        }
                    }

                    // Handle Memory (Storage Size)
                    if (strtolower($attribute['name']) === 'მეხსიერება' || strtolower($attribute['slug']) === 'pa_memory') {
                        Log::info("🟢 Memory value received from WooCommerce: {$value}");

                        // Ensure format consistency
                        if (!str_contains($value, 'GB')) {
                            $value .= 'GB';
                        }

                        Log::info("🔄 Adjusted memory value for lookup: {$value}");

                        // Fetch ID from `attribute_values` table
                        $attributeValue = AttributeValues::where('attribute_id', 1)
                            ->where('value', $value)
                            ->first();

                        if ($attributeValue) {
                            Log::info("✅ Found attribute ID: {$attributeValue->id} for memory value: {$value}");
                            $variantId = (string) $attributeValue->id;

                            if (!in_array($variantId, $selectedVariantIds)) {
                                $selectedVariantIds[] = $variantId;
                                $selectedVariants[1][] = $variantId;
                            }
                        } else {
                            Log::warning("⚠️ No matching attribute ID found for memory value: {$value}");
                        }
                    }
                }
            }
        }
    }

    // ✅ Ensure Variants Are Saved Properly
    $hasVariants = !empty($selectedVariantIds) ? 1 : 0;

    // ✅ Process Images Using WooCommerceService
    $imageData = $this->wooCommerceService->saveImages($apiProduct['images']);

    try {
        $product->fill([
            'category_id'            => $categoryId,
            'brand_id'               => $brandId,
            'user_id'                => 1,
            'created_by'             => 1,
            'slug'                   => $slug,
            'price'                  => $price,
            'special_discount'       => $specialDiscount,
            'barcode'                => $barcode, // ✅ Barcode from SKU
            'has_variant'            => $hasVariants,  // ✅ Ensuring `has_variant` is set correctly
            'current_stock'          => $totalStock,
            'stock_visibility'       => $stockVisibility, // ✅ Correct stock visibility
            'total_sale'             => $apiProduct['total_sales'] ?? 0,
            'status'                 => ($apiProduct['status'] === 'publish') ? 'published' : 'unpublished',
            'is_approved'            => 1,
            'rating'                 => floatval($apiProduct['rating_count'] ?? 0),
            'is_deleted'             => 0,

            // ✅ Store Colors and Attribute Sets
            'colors'                 => !empty($colorIds) ? $colorIds : [],
            'attribute_sets'         => ["1"], // Default Attribute Set

            // ✅ Store Selected Variants & IDs Properly
            'selected_variants'      => !empty($selectedVariants[1]) ? $selectedVariants : ["1" => []],
            'selected_variants_ids'  => !empty($selectedVariantIds) ? $selectedVariantIds : [],

            // ✅ Store Images
            'thumbnail'              => $imageData['thumbnail'],
            'images'                 => $imageData['image_objects'],
            'thumbnail_id'           => $imageData['thumbnail_id'],
            'image_ids'              => implode(',', $imageData['image_ids']),
        ]);

        $product->save();

        Log::info("✅ Successfully saved product: {$apiProduct['name']} with ID: " . $product->id);
        return $product;
    } catch (\Exception $e) {
        Log::error("❌ Error saving product {$apiProduct['name']}: " . $e->getMessage());
        return null;
    }
}







private function storeProductVariants(Product $product, array $variations)
{
    Log::info("Storing product variants for product ID: {$product->id}");

    foreach ($variations as $variantData) {
        if (!isset($variantData['id'])) {
            Log::error("Skipping variation: Missing ID");
            continue;
        }

        $variationId = $variantData['id'];
        Log::info("Processing variation ID: $variationId");

        $variantAttributes = [];
        $variantIds = [];
        $colorName = null;
        $memoryValue = null;
        $colorId = null;

        if (!empty($variantData['attributes'])) {
            foreach ($variantData['attributes'] as $attribute) {
                if (!empty($attribute['option'])) {
                    $value = trim($attribute['option']); // Trim spaces

                    // Handle Memory (Storage Size)
                    if (strtolower($attribute['name']) === 'მეხსიერება') {
                        if (!str_contains($value, 'GB')) {
                            $value .= 'GB';
                        }
                        $memoryValue = $value; // Save memory
                    }

                    // Handle Color
                    if ($attribute['name'] === 'ფერი') {
                        $colorId = ColorLanguage::where('name', $value)->value('color_id');
                        $colorName = $value; // Save color name
                    } else {
                        // Other attributes like storage variants
                        $attributeValue = AttributeValues::where('attribute_id', 1)->where('value', $value)->first();
                        if ($attributeValue) {
                            $variantIds[] = $attributeValue->id;
                        }
                    }
                }
            }
        }

        // ✅ Create Variant Name (Color - Memory)
        $variantAttributes[] = $colorName;  // Add color
        $variantAttributes[] = $memoryValue; // Add memory

        // Remove null values & join attributes
        $variantName = implode('-', array_filter($variantAttributes));

        // ✅ Process Image Data
        $imageResponse = $this->wooCommerceService->saveImages([$variantData['image']]);
        $imageId = $imageResponse['thumbnail_id'] ?? null;
        $imageVariants = $this->getImage($imageId);


        // ✅ Store in ProductStock
        $productStock = ProductStock::updateOrCreate(
            ['product_id' => $product->id, 'sku' => $variantData['id'] ?? null],
            [
                'variant_ids' => $colorId . '-' . implode('-', $variantIds),
                'name' => $variantName, // ✅ Correctly formatted name
                'current_stock' => max(0, (int) ($variantData['stock_quantity'] ?? 0)),
                'price' => floatval($variantData['price'] ?? $product->price),
                'image' => $imageVariants ?? [],
                'image_id' => $imageId,
            ]
        );
    }
}






public function deleteUnusedMedia(){

    $mediaRecords = DB::table('media')->get();

    foreach ($mediaRecords as $media) {
        $filePath = public_path($media->original_file); // Example: public/images/filename.jpg

        if (!File::exists($filePath)) {
            DB::table('media')->where('id', $media->id)->delete();
            echo "Deleted record ID: {$media->id}, File Missing: {$media->original_file}\n";
        } else {
            echo "File Exists: {$media->original_file}\n";
        }
    }
}


    
    



    
    
    
    





    public function storeColors()
    {
      $prod = Product::find(23);

        return $prod->json();
    }




    
    




    




}
