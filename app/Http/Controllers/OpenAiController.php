<?php

namespace App\Http\Controllers;

use OpenAI;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\ProductLanguage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OpenAiController extends Controller
{

    public function index() {
        return view('admin.openai.index'); // Ensure the correct Blade file name
    }


   
    
    public function updateOrCreateLang(Request $request) {
        $productName = trim($request->input('product_name'));
    
        if (!$productName) {
            return response()->json(['error' => 'Product name is required'], 400);
        }

        $records = ProductLanguage::where(function($query) {
            $query->where('description', '')
                  ->orWhereNull('description');
        })
        ->whereHas('product', function($query) {
            $query->where('category_id', 11);
        })
        ->groupBy('product_id')
        ->pluck('name'); // Ensure one record per product
        // ->get();

        dd($records);
    
        // Find product ID from product_languages using the name
        $productLang = DB::table('product_languages')
            ->where('name', $productName)
            ->first();
    
        if (!$productLang) {
            Log::error("Product not found in product_languages", ['name' => $productName]);
            return response()->json(['error' => 'Product not found'], 404);
        }
    
        $productId = $productLang->product_id;
    
        // Now fetch product details from products table
        $product = DB::table('products')->where('id', $productId)->first();
    
        if (!$product) {
            Log::error("Product not found in products table", ['product_id' => $productId]);
            return response()->json(['error' => 'Product not found'], 404);
        }
        
        // dd('found');
        $client = OpenAI::client(env('OPENAI_API_KEY'));
    
        try {
            // Generate content
            $englishContent = $this->generateContentV01($client, $product, 'en');
            $georgianContent = $this->generateContentV01($client, $product, 'ka');
    
            // Log AI responses
            Log::channel('ai')->info('AI Response for English', [
                'product_id' => $productId,
                'response' => $englishContent
            ]);
            Log::channel('ai')->info('AI Response for Georgian', [
                'product_id' => $productId,
                'response' => $georgianContent
            ]);
    
            // Update database
            $enUpdated = $this->updateProductLanguage($productId, 'en', $englishContent);
            $kaUpdated = $this->updateProductLanguage($productId, 'ka', $georgianContent);
    
            if (!$enUpdated || !$kaUpdated) {
                Log::error("Failed to save content", [
                    'product_id' => $productId,
                    'en_success' => $enUpdated,
                    'ka_success' => $kaUpdated
                ]);
            }
    
            return response()->json([
                'success' => true,
                'product_id' => $productId,
                'en' => $englishContent,
                'ka' => $georgianContent
            ]);
    
        } catch (\Exception $e) {
            Log::error("AI Content Generation Failed", [
                'product_id' => $productId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    
    protected function generateContentV01($client, $product, $language) 
    {
        try {
            // Define language-specific templates
            $short_desc_template = [
                'ka' => "<ul><li>ეკრანი: [size, type]</li><li>RAM: [size]</li><li>ელემენტი: [element apacity]</li><li>კამერა: [specs]</li></ul>",
                'en' => "<ul><li>Display: [size, type]</li><li>RAM: [size]</li><li>Battery: [element capacity]</li><li>Camera: [specs]</li></ul>"
            ];
    
            $prompt = "Generate smartphone technical specifications in {$language} as VALID JSON with perfect SEO structure. Follow these rules EXACTLY:

                1. **SEO PRIORITIES**:
                   - Meta Title: Include brand + model + 2-3 key specs (under 60 chars)
                   - Meta Description: Benefits-focused summary with specs (under 160 chars)
                   - Keywords: Top 5 commercial search terms for this model
                
                2. **TECHNICAL REQUIREMENTS**:
                   - UTF-8 encoding mandatory
                   - Use EXACT format for short_description:
                     {$short_desc_template[$language]}
                   - Description must be comprehensive HTML specs list
                
                3. **JSON STRUCTURE**:
                {
                    \"short_description\": \"{$short_desc_template[$language]}\",
                    \"description\": \"<ul><li><strong>Display:</strong> [Size, Type, Resolution, Refresh Rate]</li><li><strong>Performance:</strong> [CPU, GPU, RAM Type]</li><li><strong>Storage:</strong> [Capacity, Type]</li><li><strong>Cameras:</strong> [All lenses with MP + features]</li><li><strong>Battery:</strong> [Capacity, Charging Tech]</li><li><strong>Connectivity:</strong> [5G, WiFi, Bluetooth versions]</li><li><strong>OS:</strong> [Version + upgrade path]</li><li><strong>Dimensions:</strong> [Weight, Size]</li><li><strong>Durability:</strong> [IP rating, Materials]</li></ul>\",
                    \"tags\": \"[5-7 high-traffic keywords in {$language}]\",
                    \"unit\": \"".($language === 'ka' ? 'ცალი' : 'pc')."\",
                    \"meta_title\": \"[Brand Model] - [Key Spec1] + [Key Spec2]\",
                    \"meta_description\": \"[Brand Model] featuring [3 top features]. Includes [spec1], [spec2], and [spec3]. [Unique selling point].\",
                    \"meta_keywords\": \"[brand], [model], [spec1], [spec2], [spec3], [price_range]\"
                }
                
                4. **LANGUAGE-SPECIFIC RULES**:
                   ".($language === 'ka' ? 
                     "- Georgian text must use მხედრული script exclusively
                      - Include local market terms (e.g., 'სუპერ დამტენი' for fast charging)
                      - Prioritize keywords used in alta.ge/search" : 
                     "- Use standard international spec terminology
                      - Include both technical and commercial keywords
                      - Optimize for Amazon/eBay search trends")."
                
                5. **VALIDATION**:
                   - No marketing fluff - ONLY technical facts
                   - All measurements in correct units (mm/mAh/GHz)
                   - Escape all double quotes in JSON
                   - Confirm character limits for meta fields"
                   
                   
            ;
    
            $response = $client->completions()->create([
                'model' => 'gpt-3.5-turbo-instruct',
                'prompt' => $prompt,
                'max_tokens' => 2000,
                'temperature' => 0.3,
            ]);
    
            return $this->processAiResponse($response['choices'][0]['text'], $language);
    
        } catch (\Exception $e) {
            Log::error("AI Generation Error", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    
    protected function processAiResponse($rawResponse, $language)
    {
        // Normalize encoding
        $cleaned = mb_convert_encoding($rawResponse, 'UTF-8', 'UTF-8');
        $cleaned = preg_replace('/[^\x{0000}-\x{FFFF}]/u', '', $cleaned);
        
        // Extract JSON
        if (preg_match('/\{(?:[^{}]|(?R))*\}/x', $cleaned, $matches)) {
            $json = $matches[0];
        } else {
            throw new \Exception("No valid JSON found in response");
        }
    
        // Fix common issues
        $json = str_replace(["\n", "\t"], '', $json);
        $json = preg_replace('/,\s*}/', '}', $json); // Remove trailing commas
        
        // Decode with error handling
        $data = json_decode($json, true, 512, JSON_INVALID_UTF8_SUBSTITUTE);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("JSON decode error: ".json_last_error_msg());
        }
    
        // Ensure required fields
        $required = ['name', 'short_description', 'description', 'tags', 'unit'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                $data[$field] = ($field === 'unit') ? ($language === 'ka' ? 'ცალი' : 'pc') : '';
            }
        }
    
        // Clean descriptions
        $data['short_description'] = strip_tags($data['short_description'], '<ul><li>');
        $data['description'] = strip_tags($data['description'], '<ul><li>');
        
        return $data;
    }
    
    protected function updateProductLanguage($productId, $lang, $content) 
    {
        try {
            return DB::table('product_languages')->updateOrInsert(
                ['product_id' => $productId, 'lang' => $lang],
                array_merge($content, ['updated_at' => now()])
            );
        } catch (\Exception $e) {
            Log::error("DB Update Failed", [
                'product_id' => $productId,
                'lang' => $lang,
                'content' => $content,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }



    

    public function processJson(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'json_data' => 'required|json', // Now accepting a single JSON string
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator);
        }
    
        try {
            $results = [
                'total_updated' => 0,
                'products_processed' => 0,
                'processed_langs' => [],
                'product_details' => []
            ];
            
            DB::beginTransaction();
    
            $decodedData = json_decode($request->json_data, true, 512, JSON_THROW_ON_ERROR);
    
            // Check if we got an array of products
            if (!is_array($decodedData)) {
                throw new \Exception('The JSON data must be an array of product objects');
            }
    
            // Process each product in the array
            foreach ($decodedData as $productData) {
                $this->processSingleProduct($productData, $results);
            }
    
            DB::commit();
    
            $message = sprintf(
                "Processed %d products. Updated %d language records (%s).",
                $results['products_processed'],
                $results['total_updated'],
                implode(', ', array_unique($results['processed_langs']))
            );
    
            return back()
                ->with('success', $message)
                ->with('process_details', $results['product_details']);
    
        } catch (\JsonException $e) {
            return back()->withErrors(['json_data' => 'Invalid JSON format: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Processing failed: ' . $e->getMessage()]);
        }
    }
    
    protected function processSingleProduct(array $productData, array &$results)
    {
        $validator = Validator::make($productData, [
            'product_name' => 'required|string|max:255',
            'records' => 'required|array|min:1',
            'records.*.lang' => 'required|in:en,ka',
            'records.*.name' => 'required|string|max:255',
            'records.*.short_description' => 'required|string',
            'records.*.description' => 'required|string',
            'records.*.meta_title' => 'required|string|max:255',
            'records.*.meta_description' => 'required|string',
            'records.*.meta_keywords' => 'required|string',
        ]);
    
        if ($validator->fails()) {
            throw new \Exception('Invalid product structure for: ' . ($productData['product_name'] ?? 'unknown') . ' - ' . $validator->errors()->first());
        }
    
        $productName = $productData['product_name'];
        $productResults = [
            'name' => $productName,
            'updated' => 0,
            'langs' => []
        ];
    
        foreach ($productData['records'] as $record) {
            $affected = ProductLanguage::where('name', $productName)
                ->where('lang', $record['lang'])
                ->update([
                    'short_description' => $record['short_description'],
                    'description' => $record['description'],
                    'tags' => $record['tags'] ?? '',
                    'meta_title' => $record['meta_title'],
                    'meta_description' => $record['meta_description'],
                    'meta_keywords' => $record['meta_keywords'],
                    'updated_at' => now()
                ]);
    
            if ($affected) {
                $results['total_updated'] += $affected;
                $productResults['updated'] += $affected;
                $productResults['langs'][] = $record['lang'];
                $results['processed_langs'][] = $record['lang'];
            }
        }
    
        $results['products_processed']++;
        $results['product_details'][] = $productResults;
    }


    // public function processJson(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'json_data' => 'required|array|min:1',
    //         'json_data.*' => 'required|json', // Validates each element in the array is JSON
    //     ]);
        
    //     if ($validator->fails()) {
    //         return back()->withErrors($validator);
    //     }
    
    //     try {
    //         $updatedCount = 0;
    //         $processedLangs = [];
            
    //         DB::beginTransaction();
    
    //         foreach ($request->json_data as $jsonFile) {
    //             $data = json_decode($jsonFile, true, 512, JSON_THROW_ON_ERROR);
    
    //             // Validate the structure of the JSON data
    //             $validator = Validator::make($data, [
    //                 'product_name' => 'required|string|max:255',
    //                 'records' => 'required|array|min:1',
    //                 'records.*.lang' => 'required|in:en,ka',
    //                 'records.*.name' => 'required|string|max:255'
    //             ]);
    
    //             if ($validator->fails()) {
    //                 return back()->withErrors(['json_data' => 'Invalid JSON structure']);
    //             }
    
    //             $productName = $data['product_name'];
    
    //             foreach ($data['records'] as $record) {
    //                 $affected = ProductLanguage::where('name', $productName)
    //                     ->where('lang', $record['lang'])
    //                     ->update([
    //                         'short_description' => $record['short_description'],
    //                         'description' => $record['description'],
    //                         'tags' => $record['tags'] ?? '',
    //                         'meta_title' => $record['meta_title'],
    //                         'meta_description' => $record['meta_description'],
    //                         'meta_keywords' => $record['meta_keywords'],
    //                         'updated_at' => now()
    //                     ]);
    
    //                 if ($affected) {
    //                     $updatedCount++;
    //                     $processedLangs[] = $record['lang'];
    //                 }
    //             }
    //         }
    
    //         DB::commit();
    
    //         return back()->with('success', sprintf(
    //             "Updated %d language records (%s) for multiple files",
    //             $updatedCount,
    //             implode(', ', array_unique($processedLangs))
    //         ));
    
    //     } catch (\JsonException $e) {
    //         return back()->withErrors(['json_data' => 'Invalid JSON format']);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return back()->withErrors(['error' => 'Update failed: '.$e->getMessage()]);
    //     }
    // }
    
}