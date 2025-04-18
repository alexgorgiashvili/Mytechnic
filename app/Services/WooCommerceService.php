<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\ProductLanguage;
use Automattic\WooCommerce\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Repositories\Interfaces\Admin\MediaInterface;
use App\Repositories\Admin\Product\ProductLanguageRepository;

class WooCommerceService
{
    protected $apiUrl;
    protected $consumerKey;
    protected $consumerSecret;
    protected $productLanguageRepo;
    protected $medias;
    protected $woocommerce;

    public function __construct(ProductLanguageRepository $productLanguageRepo,MediaInterface $medias,)
    {
        // Set your WooCommerce store details
        $this->apiUrl = env('WC_API_URL');
        $this->consumerKey = env('WC_CONSUMER_KEY');
        $this->consumerSecret = env('WC_CONSUMER_SECRET');
        $this->productLanguageRepo = $productLanguageRepo;
        $this->medias = $medias;

        $this->initializeClient();

    }

    protected function initializeClient()
    {
        $this->woocommerce = new Client(
            $this->apiUrl,
            $this->consumerKey,
            $this->consumerSecret,
            [
                'version' => 'wc/v3',
                'timeout' => 30,
                'verify_ssl' => false // Only for development, remove in production
            ]
        );
    }

 
  

    /**
     * Fetch all products from WooCommerce.
     */
    public function getAllProducts($perPage = 30, $page = 1)
    {
        $response = Http::withOptions([
            'verify' => false, // Disables SSL verification
        ])->withBasicAuth($this->consumerKey, $this->consumerSecret)
          ->get("{$this->apiUrl}products", [
              'per_page' => $perPage,
              'page' => $page,
          ]);

        return $response->json();
    }

    /**
     * Fetch a single product by ID.
     */
    // public function getProductById($id)
    // {
    //     $response = Http::withOptions([
    //         'verify' => false, // Disables SSL verification
    //     ])->withBasicAuth($this->consumerKey, $this->consumerSecret)
    //         ->get("{$this->apiUrl}products/{$id}");

    //     return $response->json();
    // }
    public function getProductById($id)
    {
        try {
            return $this->woocommerce->get("products/{$id}");
        } catch (\Exception $e) {
            Log::error("WooCommerce API error: " . $e->getMessage());
            return null;
        }
    }

    public function getAllProductStocks($perPage = 30, $page = 1)
    {

        $productId = 35968; // Replace with your product ID
        $product = $this->woocommerce->get("products/{$productId}");
        $variations = $this->woocommerce->get("products/{$productId}/variations", ['per_page' => 100]);

        dd($variations);
        $response = $this->woocommerce->get('products', [
            'per_page' => $perPage,
            'page' => $page
        ]);
        $stocks = [];
    
        foreach ($response as $product) {
            if ($product->type === 'simple') {
                $stocks[] = [
                    'woo_id' => $product->id,
                    'sku' => $product->sku,
                    'stock_quantity' => $product->stock_quantity,
                    'type' => 'simple',
                    'name' => $product->name
                ];
            }
    
            if ($product->type === 'variable') {
                // Fetch variations for this variable product
                $variations = $this->woocommerce->get("products/{$product->id}/variations");
    
                foreach ($variations as $variation) {
                    $variantName = $product->name;
                    if (!empty($variation->attributes)) {
                        $attrs = array_map(fn($a) => $a->option, $variation->attributes);
                        $variantName .= ' - ' . implode(' / ', $attrs);
                    }
    
                    $stocks[] = [
                        'woo_id' => $variation->id,
                        'sku' => $variation->sku,
                        'stock_quantity' => $variation->stock_quantity,
                        'type' => 'variation',
                        'name' => $variantName
                    ];
                }
            }
        }
    
        return $stocks;
    }
    

    public function getProductVariations($productId, $perPage = 50)
    {
        $page = 1;
        $allVariations = [];

        do {
            $response = Http::withOptions([
                'verify' => false, // Disables SSL verification
            ])->withBasicAuth($this->consumerKey, $this->consumerSecret)
                ->get("{$this->apiUrl}products/{$productId}/variations", [
                    'per_page' => $perPage,
                    'page' => $page,
                ]);

            $variations = $response->json();

            if (!is_array($variations) || empty($variations)) {
                break; // Stop if no more variations
            }

            $allVariations = array_merge($allVariations, $variations);
            $page++;
            sleep(1); // Prevent API rate limits

        } while (count($variations) === $perPage);

        return $allVariations;
    }

   

    public function saveImages($images, $type = 'image')
    {
        $imageResults = [];
        $imageObjects = [];

        if (!empty($images)) {
            foreach ($images as $image) {
                $imageUrl = $image['src'];
                $storedImage = $this->medias->storeHttp($imageUrl, $type);

                Log::info("Stored Image Response: " . json_encode($storedImage));

                if (is_array($storedImage) && isset($storedImage['id'])) {
                    $imageResults[] = $storedImage['id'];

                    $imageObjects[] = [
                        "storage" => "local",
                        "original_image" => $storedImage['images']['original_image'],
                        "image_40x40" => $storedImage['images']['image_40x40'] ?? null,
                        "image_72x72" => $storedImage['images']['image_72x72'] ?? null,
                        "image_190x230" => $storedImage['images']['image_190x230'] ?? null,
                        "image_110x122" => $storedImage['images']['image_110x122'] ?? null,
                        "image_320x320" => $storedImage['images']['image_320x320'] ?? null,
                        "image_387x280" => $storedImage['images']['image_387x280'] ?? null,
                        "image_thumbnail" => $storedImage['images']['image_thumbnail'] ?? null,
                        "image_1200x630" => $storedImage['images']['image_1200x630'] ?? null,
                    ];
                } else {
                    Log::warning("Failed to store image: $imageUrl");
                }
            }
        }

        // Return images in proper format
        return [
            'image_ids' => $imageResults,
            'image_objects' => $imageObjects,
            'thumbnail_id' => !empty($imageResults) ? $imageResults[0] : null,
            'thumbnail' => !empty($imageObjects) ? $imageObjects[0] : ["storage" => "local", "original_image" => "default.jpg"]
        ];
    }

    public function storeProductLanguage($id, array $apiProduct)
    {
        $languages = [
            'en' => $apiProduct['name'] ?? 'Unnamed Product',
            'ka' => $apiProduct['name'] ?? 'Unnamed Product',
        ];
    
        foreach ($languages as $lang => $name) {
            Log::info("🌐 Saving ProductLanguage for {$lang} | Name: {$name}");
    
            $result = ProductLanguage::updateOrCreate(
                [
                    'product_id' => $id,
                    'lang' => $lang,
                ],
                [
                    'name' => $name,
                    'short_description' => $apiProduct['short_description'] ?? null,
                    'description' => $apiProduct['description'] ?? null,
                    'unit' => 'pc',
                    'meta_title' => $name,
                    'meta_description' => $apiProduct['short_description'] ?? null,
                    'meta_keywords' => null,
                ]
            );
    
            Log::info("✅ ProductLanguage {$lang} " . ($result->wasRecentlyCreated ? 'created' : 'updated'));
        }
    }
    



    //  public function storeProductLanguage(Product $product, array $apiProduct)
    // {
    //     $requestData = [
    //         'product_id' => $product->id,
    //         'lang' => ['ka'], // Default language
    //         'name' => $apiProduct['name'],
    //         'description' => $apiProduct['description'] ?? '',
    //         'short_description' => $apiProduct['short_description'] ?? '',
    //         'tags' => '',
    //         'unit' => '',
    //         'meta_title' => $apiProduct['name'], // Use product name as meta title
    //         'meta_description' => '',
    //         'meta_keywords' => '',
    //     ];

    //     // Call ProductLanguageRepository to store translations
    //     $this->productLanguageRepo->store(new Request($requestData));
    // }



    // public function saveColorsFromWooCommerce()
    // {
    //     // Fetch products from WooCommerce
    //     $products = $this->getAllProducts(100, 1);
    
    //     if (!$products || !is_array($products)) {
    //         Log::error("WooCommerce API returned an invalid response.");
    //         return "No products found!";
    //     }
    
    //     $colors = [];
    
    //     // Extract colors from the attributes
    //     foreach ($products as $product) {
    //         if (!empty($product['attributes'])) {
    //             foreach ($product['attributes'] as $attribute) {
    //                 if ($attribute['slug'] === 'pa_color') { // Corrected slug check
    //                     foreach ($attribute['options'] as $color) {
    //                         $color = trim($color);
    //                         if (!empty($color)) {
    //                             $colors[] = $color;
    //                         }
    //                     }
    //                 }
    //             }
    //         }
    //     }
    
    //     // Remove duplicates
    //     $colors = array_unique($colors);
    
    //     Log::info("Extracted Colors: ", $colors);
    
    //     foreach ($colors as $colorName) {
    //         // Check if the color already exists in color_languages table
    //         $existingColor = DB::table('color_languages')->where('name', $colorName)->first();
    
    //         if (!$existingColor) {
    //             // Insert new color in colors table
    //             $colorId = DB::table('colors')->insertGetId([
    //                 'code' => '#000000', // Temporary placeholder
    //                 'created_at' => now(),
    //                 'updated_at' => now(),
    //             ]);
    
    //             // Insert into color_languages
    //             DB::table('color_languages')->insert([
    //                 'name' => $colorName,
    //                 'color_id' => $colorId,
    //                 'lang' => 'en', // Assuming default is English
    //                 'created_at' => now(),
    //                 'updated_at' => now(),
    //             ]);
    
    //             Log::info("Inserted Color: {$colorName}, Color ID: {$colorId}");
    //         } else {
    //             Log::info("Color '{$colorName}' already exists, skipping.");
    //         }
    //     }
    
    //     return "Colors fetched and saved successfully.";
    // }
    
    // public function updateColorCodes()
    // {
    //     // Mapping of color_id to HEX codes
    //     $colorHexMappings = [
    //         1 => "#F3D6E4", 2 => "#222222", 3 => "#6A5ACD", 4 => "#0000FF", 5 => "#808080",
    //         6 => "#FFC0CB", 7 => "#800080", 8 => "#EAEAEA", 9 => "#1C1C1C", 10 => "#2E8B57",
    //         11 => "#C0C0C0", 12 => "#A020F0", 13 => "#4B4B4B", 14 => "#E6C7C2", 15 => "#3D3D3D",
    //         16 => "#D2B48C", 17 => "#8B5A2B", 18 => "#E0E0E0", 19 => "#98FF98", 20 => "#C0C0C0",
    //         21 => "#FFFF00", 22 => "#000000", 23 => "#008080", 24 => "#3F00FF", 25 => "#FFFFFF",
    //         26 => "#F5F5DC", 27 => "#800020", 28 => "#5F8575", 29 => "#FFFDD0", 30 => "#AFEEEE",
    //         31 => "#008000", 32 => "#E6E6FA", 33 => "#FFD700", 34 => "#191970", 35 => "#FF0000",
    //         36 => "#FFDAB9", 37 => "#FFC0CB", 38 => "#C0C0C0", 39 => "#0000FF", 40 => "#808080",
    //         41 => "#000000", 42 => "#EE82EE", 43 => "#A9A9A9", 44 => "#7D7DFF", 45 => "#FFD700",
    //         46 => "#E6E6FA", 47 => "#008000", 48 => "#FFFF00", 49 => "#AFEEEE", 50 => "#FFFFFF",
    //         51 => "#FF0000", 52 => "#EE82EE"
    //     ];
    
    //     // Update colors table with HEX codes
    //     foreach ($colorHexMappings as $colorId => $hexCode) {
    //         DB::table('colors')
    //             ->where('id', $colorId)
    //             ->update(['code' => $hexCode, 'updated_at' => now()]);
    //     }
    
    //     Log::info("Color codes updated successfully!");
        
    //     return "Color codes updated successfully!";
    // }



}
