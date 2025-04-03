<?php

namespace App\Http\Controllers\Admin\Product;

use Carbon\Carbon;
use App\Models\Brand;
use App\Models\Category;
use App\Traits\SlugTrait;
use App\Models\ReviewReply;
use App\Models\ProductStock;
use Illuminate\Http\Request;
use App\Models\ShippingClass;
use App\Utility\VariantUtility;
use App\Models\CategoryLanguage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Exception\ConnectException;
use App\Repositories\Admin\VatTaxRepository;
use Stichoza\GoogleTranslate\GoogleTranslate;
use App\Repositories\Interfaces\Admin\MediaInterface;
use App\Repositories\Interfaces\Site\ReviewInterface;
use App\Repositories\Interfaces\Admin\SellerInterface;
use App\Http\Requests\Admin\Product\ProductStoreRequest;
use App\Repositories\Interfaces\Admin\LanguageInterface;
use App\Repositories\Admin\Addon\ShippingClassRepository;
use App\Repositories\Interfaces\Admin\Product\BrandInterface;
use App\Repositories\Interfaces\Admin\Product\ColorInterface;
use App\Repositories\Interfaces\Admin\Product\ProductInterface;
use App\Repositories\Interfaces\Admin\Product\CategoryInterface;
use App\Repositories\Interfaces\Admin\Product\AttributeInterface;

class ProductController extends Controller
{
    use SlugTrait;


    protected $products;
    protected $categories;
    protected $brands;
    protected $colors;
    protected $attributes;
    protected $vat_tax;
    protected $languages;
    protected $sellers;
    protected $medias;

    public function __construct(ProductInterface $products,
                                MediaInterface $medias,
                                CategoryInterface $categories,
                                BrandInterface $brands,
                                ColorInterface $colors,
                                AttributeInterface $attributes,
                                VatTaxRepository $vat_tax,
                                SellerInterface $sellers,
                                LanguageInterface $languages){
        $this->products         = $products;
        $this->categories       = $categories;
        $this->brands           = $brands;
        $this->colors           = $colors;
        $this->attributes       = $attributes;
        $this->vat_tax          = $vat_tax;
        $this->languages        = $languages;
        $this->sellers          = $sellers;
        $this->medias          = $medias;

    }

    public function createHttp(Request $request){
        // dd($request->all());

        // $dataId = 'SgWE38oXXifLP4LlM';
        //         $url = "https://api.apify.com/v2/datasets/$dataId/items?token=apify_api_f6i7MDtX689r3OswHTebsycDAXYygB1HPjb6&clean=false&fields=category%2CoriginalCategory%2CallVariants%2Cattributes%2Cbrand%2Cname%2Cgender%2Cimages%2Cprice%2CratingScore";
        //         $response = Http::get($url);
        //         $data = json_decode($response->body(), true);
        //         // echo $data['name'];
        //         // dd($item['name']);

        //         foreach($data as $item){
        //         dd($item);
        //             echo $item;
        //         }
// dd($data);

        return view('admin.products.products.create-new');
    }


    public function storeHttp(Request $request){
        // dd('store');
  
        Log::info(['order_start' => 'true']);
        // $order = json_decode($request->getContent(), true);
        // Log::info(['order' => 'test']);
        // Log::info(['order' => $order]);


        // return true;
        // $dataId = $order['resource']['defaultDatasetId'];
        $dataId = 'nr7s0aQuabOydoCiL';
        
                $url = "https://api.apify.com/v2/datasets/$dataId/items?token=apify_api_690Mflmb0CedA2kUV2h2TKHZaEwNd32DVzwE&clean=false&fields=category%2CoriginalCategory%2CallVariants%2Cattributes%2Cbrand%2Cname%2Cgender%2Cimages%2Cprice%2CratingScore";
        
                $response = Http::get($url);
                $data = json_decode($response->body(), true);
    
                $type = 'image';
                // Check if data is an array and not empty
                if (is_array($data) && !empty($data)) {
                    $results = [];
                    $name = '';
                    $price = '';
                    foreach ($data as $item) {
                        // Extract image URLs
                        $images = $item['images'] ?? [];
                        $name = $item['name'];
                        $price = $item['price']['sellingPrice']['value'];
                        // $price = floatval(str_replace(['$', ',', ' '], '', $priceText));
                        
                        foreach ($images as $imageUrl) {
        
        
                            $fullImageUrl = 'https://cdn.dsmcdn.com/' . ltrim($imageUrl, '/');
                            $result = $this->medias->storeHttp($fullImageUrl, $type);
                        
                            if (is_array($result) && isset($result['id'])) {
                                $results[] = $result['id'];
                            } else {
                                $log = Log::channel('mylog')->info(['result problem' => 'bad']);
                            }
                                                // dd($images);
                            
                        }
                    }
                    // dd($results);
                }
                $filteredData = [
                    // "_token" => "P6gluj5oQYU4GinF8yMDTexfN3zZq14vNCVoG88D",
                    // "r" => "https://www.cheap-shop.ge/admin/categories",
                    "name" => $name,
                    "category" => "4",
                    "unit" => "777",
                    "minimum_order_quantity" => "1",
                    "thumbnail" => $results[0], // Set the first ID as the thumbnail
                    "images" => implode(',', $results),
                    "price" => $price,
                    "stock_visibility" => "visible_with_text",
                    // "sku" => "S42G6XKBVNA811YCQO2210",
                    "current_stock" => "500",
                    "short_description" => "Description11ww2",
                    "shipping_type" => "free_shipping",
                    "status" => "published"
                ];
                
                $request = new Request($filteredData);
                $log = Log::channel('mylog')->info(['filteredData' => $request->all(),'price' => $price,'name' => $name]);
                // $request = $newrequest;
                // die;
                DB::beginTransaction();
                try {
                    
                   $req =  $this->products->storeHttp($request);
                    Toastr::success(__('Created Successfully'));
                    session()->forget('attributes');
                    $log = Log::channel('mylog')->info(['req' => $req]);
             
                    DB::commit();
                    return true;

                    // return redirect()->route('products');
                } catch (\Exception $e) {
                    Log::channel('mylog')->error(['Product Store Error' => $e->getMessage()]);
        
                    DB::rollBack();
                    return false;
                }
    }




    public function getProductDetail($productId)
    {
        // $response = Http::get("https://public.trendyol.com/discovery-web-productgw-service/api/productDetail/{$productId}?&culture=en-GB");
        $response = Http::get("https://public.trendyol.com/discovery-web-productgw-service/api/productDetail/{$productId}");

        return $response->json();
        // return json_decode($response->body(), true);

    }


    // protected function saveCategories($categories, $parentId = null, $lang = 'en')
    // {
    //     foreach ($categories as $cat) {
    //         // Generate a slug from the category name
    //         $slug = $this->getSlug($cat['name']);
    
    //         $category = Category::firstOrCreate(
    //             ['id' => $cat['id']],  // Use 'api_id' instead of 'id' to avoid confusion with the auto-incrementing primary key
    //             ['parent_id' => $parentId,'slug' => $slug]
    //         );
    
    //         // Save or update the language-specific data
    //         CategoryLanguage::updateOrCreate(
    //             ['category_id' => $category->id, 'lang' => $lang],
    //             ['title' => $cat['name']]
    //         );
    
    //         if (!empty($cat['subCategories'])) {
    //             $this->saveCategories($cat['subCategories'], $category->id, $lang);
    //         }
    //     }
    // }
    // protected function saveCategories($categories, $parentId = null, $lang = 'en')
    // {
    //     foreach ($categories as $cat) {
    //         // Generate a slug from the category name
    //         $slug = $this->getSlug($cat['name']);
    
    //         $category = Category::firstOrCreate(
    //             ['api_id' => $cat['id']],  // Use 'api_id' to identify the category uniquely
    //             ['parent_id' => $parentId, 'slug' => $slug]
    //         );
    
    //         // Set position if a parent is set
    //         if ($parentId) {
    //             $parent_cat = Category::where('api_id', $parentId)->first();
    //             if ($parent_cat) {
    //                 $category->position = $parent_cat->position + 1;
    //                 // Handle position overflow if necessary
    //             }
    //         }
    
    //         // Update language-specific data
    //         CategoryLanguage::updateOrCreate(
    //             ['category_id' => $category->id, 'lang' => $lang],
    //             ['title' => $cat['name']]
    //         );
    
    //         $category->save();
    
    //         // Recursively save subcategories
    //         if (!empty($cat['subCategories'])) {
    //             $this->saveCategories($cat['subCategories'], $cat['id'], $lang); // Pass API ID as parentId
    //         }
    //     }
    // }
    function translateAndSave($sourceLang, $targetLangs)
    {
        $records = CategoryLanguage::where('lang', $sourceLang)->get();
    
        foreach ($records as $record) {
            // Translation logic here
            foreach ($targetLangs as $lang) {
                    if (!CategoryLanguage::where('category_id', $record->category_id)->where('lang', $lang)->exists()) {
                        $tr = new GoogleTranslate($lang); // Set target language
                        $tr->setSource($sourceLang); // Set source language
            
                        $translatedTitle = $this->retryTranslate($tr, $record->title);
                        if (!$translatedTitle) continue; // Skip if translation failed
            
                        $newRecord = $record->replicate(); // Replicate the original record
                        $newRecord->lang = $lang; // Set new language code
                        $newRecord->title = $translatedTitle; // Set translated title
                        $newRecord->save(); // Save the new record
                    }
                }
        }
    }
    
    function retryTranslate($translator, $text, $attempts = 3)
    {
        for ($i = 0; $i < $attempts; $i++) {
            try {
                return $translator->translate($text);
            } catch (ConnectException $e) {
                if ($i === $attempts - 1) throw $e; // Rethrow the last exception if all retries fail
            }
        }
    }

    

    public function sendHttp(Request $request){
        
        // dd($prr);
        // $url = $request->url;
        // $id = 447154854;
        // $bName = $response['result']['brand']['name'];
        $categories = Http::get('https://api.trendyol.com/sapigw/product-categories');
        $dataCat= $categories->json();
        // $save = $this->saveCategories($dataCat['categories']);
        $tt = $this->translateAndSave('tr', ['en', 'ka']);

        // dd('ok');

        $id = $request->url;
        $response = $this->getProductDetail($id);
        // $dataBrand = $response->json();
        dd($response);
        
        if ($response['isSuccess']) {

            // Access the result section
            $data = $response['result'];

            $brandDetails = $data['metaBrand'];
            $brand = Brand::find($brandDetails['id']);
            if(!$brand){
    
                $storeBrand = $this->brands->storeHttp($brandDetails);
            }

            $type = 'image';
            // Check if data is an array and not empty
            if (is_array($data) && !empty($data)) {
                $results = [];
                // dd($data);

                $images = $data['images'] ?? [];

                foreach ($images as $imageUrl) {
                    

                    $fullImageUrl = 'https://cdn.dsmcdn.com/' . ltrim($imageUrl, '/');
                    $result = $this->medias->storeHttp($fullImageUrl, $type);
                
                    if (is_array($result) && isset($result['id'])) {
                        $results[] = $result['id'];
                    } else {
                        $log = Log::channel('mylog')->info(['result problem' => 'bad']);
                    }
                    
                }
            }
            
            $priorityZeroDescriptions = array_filter($data['descriptions'], function ($description) {
                return $description['priority'] === 0;
            });
    
            // Extract just the text of descriptions to pass as an array
            $descriptionsTexts = array_map(function ($desc) {
                return $desc['text'];
            }, $priorityZeroDescriptions);

            $filteredData = [
                "name" => $data['name'],
                "category" => "11",
                "brand" => $brandDetails['id'],
                "unit" => "777",
                "minimum_order_quantity" => "1",
                "thumbnail" => $results[0], 
                "images" => implode(',', $results),
                "price" => $data['price']['sellingPrice']['value'],
                "stock_visibility" => "visible_with_text",
                // "sku" => "S42G6XKBVNA811YCQO2210",
                "current_stock" => "500",
                "short_description" => $descriptionsTexts,
                "shipping_type" => "free_shipping",
                "status" => "published"
            ];
            // dd($filteredData);
            $request = new Request($filteredData);

            DB::beginTransaction();
            try {
                
               $req =  $this->products->storeHttp($request);
                Toastr::success(__('Created Successfully'));

         
                DB::commit();
                return back();

            } catch (\Exception $e) {
                // Log::channel('mylog')->error(['Product Store Error' => $e->getMessage()]);
    
                DB::rollBack();
                Toastr::error($e->getMessage());
                return back();            }
        
        } 



    }

    public function sendForScrape(){
        // $task = "https://api.apify.com/v2/acts/yeyo~trendyol-scraper/runs?token=apify_api_690Mflmb0CedA2kUV2h2TKHZaEwNd32DVzwE";
        // $input = [
        //             "input" => [
        //                 [ 
        //                     "url" => $url,
                    
        //                 ],
        //             ],
        //             "maxItems" => 10,
        //             "proxyConfiguration" => [ "useApifyProxy" => true ],
        //         ];
        //     $response = Http::post($task,$input);
        //     $data = json_decode($response, true);
        //     // Log::info(['log' => $data]);

        //     // dd($data);
        //     if($response->successful()){
        //         Toastr::success(__('Created Successfully'));

        //         return back();
        //     }
            // dd('wtf');
    }

    public function index(Request $request, $status = null){
        try {
            $products           = $this->products->paginate($request, $status ,get_pagination('pagination'),'');

            $selected_category  = isset($request->c) ? $this->categories->get($request->c) : null;
            $selected_seller    = isset($request->sl) ? $this->sellers->getSeller($request->sl) : null;

            return view('admin.products.products.index', compact('status','products','selected_category','selected_seller'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }
    public function adminProducts(Request $request, $status = null){
        try {
            $products       = $this->products->paginate($request, $status ,get_pagination('pagination'),'admin');
            $sellers        = $this->sellers->all()->where('is_user_banned',0)->where('status',1)->get();

            $selected_category  = isset($request->c) ? $this->categories->get($request->c) : null;

            return view('admin.products.products.admin-products', compact('status','products','selected_category','sellers'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }
    public function sellerProducts(Request $request, $status = null){
        try {
            $products           = $this->products->paginate($request, $status ,get_pagination('pagination'),'seller');

            $selected_category  = isset($request->c) ? $this->categories->get($request->c) : null;
            $selected_seller    = isset($request->sl) ? $this->sellers->getSeller($request->sl) : null;

            return view('admin.products.products.seller-products', compact('status','products','selected_category','selected_seller'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }
    public function digitalProducts(Request $request, $status = null){
        try {
            $products           = $this->products->paginate($request, $status ,get_pagination('pagination'),'digital');

            $selected_category  = isset($request->c) ? $this->categories->get($request->c) : null;
            $selected_seller    = isset($request->sl) ? $this->sellers->getSeller($request->sl) : null;

            return view('admin.products.products.digital-products', compact('status','products','selected_category','selected_seller'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }
    public function catalogProducts(Request $request, $status = null){
        try {
            $products       = $this->products->paginate($request, $status ,\Config::get('yrsetting.paginate'),'catalog');

            $selected_category  = isset($request->c) ? $this->categories->get($request->c) : null;
            $selected_seller    = isset($request->sl) ? $this->sellers->getSeller($request->sl) : null;

            return view('admin.products.products.catalog-products', compact('status','products','selected_category','selected_seller'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function classifiedProducts(Request $request, $status = null){
        try {
            $products           = $this->products->paginate($request, $status ,\Config::get('yrsetting.paginate'),'classified');
            $selected_category  = isset($request->c) ? $this->categories->get($request->c) : null;
            $selected_seller    = isset($request->sl) ? $this->sellers->getSeller($request->sl) : null;
            return view('admin.products.products.classified-products', compact('status','products','selected_category','selected_seller'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function create(Request $request)
    {
        try {
            $data = [
                'categories' => $this->categories->allCategory()->where('parent_id', null)->where('status', 1),
                'brands' => $this->brands->all()->where('lang', 'en')->where('status', 1)->get(),
                'colors' => $this->colors->all()->where('lang', 'en')->get(),
                'attributes' => $this->attributes->all()->where('lang', 'en')->get(),
                'campaigns' => \App\Models\Campaign::where('status', 1)->where('end_date', '>', Carbon::now()->format('Y-m-d'))->get(),
                'r' => $request->r != '' ? $request->r : $request->server('HTTP_REFERER')
            ];
            if (addon_is_activated('ramdhani')) {
                $repo = new ShippingClassRepository();
                $data['shipping_classes'] = $repo->activeClasses();
            }
            return view('admin.products.products.form', $data);
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }
    public function createDigitalProduct(Request $request)
    {
        $data = [
            'categories'    => $this->categories->allCategory()->where('parent_id', null)->where('status',1),
            'brands'        => $this->brands->all()->where('lang','en')->where('status',1)->get(),
            'colors'        => $this->colors->all()->where('lang', 'en')->get(),
            'attributes'    => $this->attributes->all()->where('lang', 'en')->get(),
            'campaigns'     => \App\Models\Campaign::where('status', 1)->where('end_date','>',Carbon::now()->format('Y-m-d'))->get(),
            'r'             => $request->r != ''? $request->r : $request->server('HTTP_REFERER'),
            'is_digital'    => 1
        ];

        return view('admin.products.products.form',$data);
    }
    public function createCatalogProduct(Request $request)
    {
        $data = [
            'categories'    => $this->categories->allCategory()->where('parent_id', null)->where('status',1),
            'brands'        => $this->brands->all()->where('lang','en')->where('status',1)->get(),
            'colors'        => $this->colors->all()->where('lang', 'en')->get(),
            'attributes'    => $this->attributes->all()->where('lang', 'en')->get(),
            'campaigns'     => \App\Models\Campaign::where('status', 1)->where('end_date','>',Carbon::now()->format('Y-m-d'))->get(),
            'r'             => $request->r != ''? $request->r : $request->server('HTTP_REFERER'),
            'is_catalog'    => 1
        ];

        return view('admin.products.products.form',$data);
    }
    public function createClassifiedProduct(Request $request)
    {
        $data = [
            'categories'    => $this->categories->allCategory()->where('parent_id', null)->where('status',1),
            'brands'        => $this->brands->all()->where('lang','en')->where('status',1)->get(),
            'colors'        => $this->colors->all()->where('lang', 'en')->get(),
            'attributes'    => $this->attributes->all()->where('lang', 'en')->get(),
            'campaigns'     => \App\Models\Campaign::where('status', 1)->where('end_date','>',Carbon::now()->format('Y-m-d'))->get(),
            'r'             => $request->r != ''? $request->r : $request->server('HTTP_REFERER'),
            'is_classified' => 1
        ];

        return view('admin.products.products.form',$data);
    }

    public function edit($id, Request $request){
        try {
           // session()->forget('attributes');
            $languages  = $this->languages->all()->orderBy('id', 'asc')->get();

            $lang       = $request->lang != '' ? $request->lang : app()->getLocale();
            if ($this->products->get($id) && $product_language = $this->products->getByLang($id, $lang)):
                $data = [
                    'categories'        => $this->categories->allCategory()->where('parent_id', null)->where('status',1),
                    'brands'            => $this->brands->all()->where('lang','en')->where('status',1)->get(),
                    'colors'            => $this->colors->all()->where('lang', 'en')->get(),
                    'attributes'        => $this->attributes->all()->where('lang', 'en')->get(),
                    'campaigns'         => \App\Models\Campaign::where('status', 1)->where('end_date','>',Carbon::now()->format('Y-m-d'))->get(),
                    'r'                 => $request->r != ''? $request->r : $request->server('HTTP_REFERER'),
                    'languages'         => $languages,
                    'product_language'  => $product_language,
                    'lang'              => $lang
                ];
                if (addon_is_activated('ramdhani')) {
                    $repo = new ShippingClassRepository();
                    $data['shipping_classes'] = $repo->activeClasses();
                }
                return view('admin.products.products.edit',$data);

            else:
                Toastr::error(__('Not found'));
                return back();
            endif;
        } catch (\Exception $e){
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function statusChange(Request $request)
    {
        if (isDemoServer()):
            $response['message']    = __('This function is disabled in demo server.');
            $response['title']      = __('Ops..!');
            $response['status']     = 'error';
            return response()->json($response);
        endif;

        DB::beginTransaction();
        try {
            $this->products->statusChange($request['data']);
            $response['message']    = __('Updated Successfully');
            $response['title']      = __('Success');
            $response['status']     = 'success';
            DB::commit();
            return response()->json($response);
        } catch (\Exception $e){
            DB::rollBack();
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function store(ProductStoreRequest $request)
    {
        if (isDemoServer()):
            Toastr::info(__('This function is disabled in demo server.'));
            return redirect()->back();
        endif;

        // dd($request->all());
        DB::beginTransaction();
        try {
            $this->products->store($request);
            Toastr::success(__('Created Successfully'));
            session()->forget('attributes');
            DB::commit();
            return redirect()->route('products');
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function update(Request $request)
    {
        if (isDemoServer()):
            Toastr::info(__('This function is disabled in demo server.'));
            return redirect()->back();
        endif;

        DB::beginTransaction();
        try {

            session()->forget('attributes');
            if ($request->variant_ids && count($request->variant_ids)) {
                ProductStock::where('product_id', $request->id)->delete();
                session()->put('attributes', count($request->variant_ids));
            }

            $product = $this->products->get($request->id);

            $validator = Validator::make($request->all(), [
                'name' => 'required|max:190',
                'slug' => 'nullable|nullable|max:190|unique:products,slug,' . $request->id,
                'category' => 'required',
                'price' => 'numeric|required',
                'unit' => 'required',
                'variant_sku.*' => 'required_if:has_variant,1|distinct|unique:product_stocks,sku',
                'video_url' => 'required_with:video_provider',
                'minimum_order_quantity' => 'numeric|min:1',
                'low_stock_to_notify' => 'numeric|min:0',
                'shipping_fee' => 'required_if:shipping_type,flat_rate',
                'special_discount_period' => 'required_with:special_discount_type',
                'special_discount' => 'required_with:special_discount_type',
                'campaign_discount' => 'required_with:campaign',
                'campaign_discount_type' => 'required_with:campaign'
            ]);

            DB::commit();

            if ($validator->fails()) {
                DB::rollBack();
                return back()->withInput()->withErrors($validator);
            }

            if (!$request->has_variant && $product->stock()->first()) {
                $sku_validator = Validator::make($request->all(), [
                    'sku' => 'required_without_all:has_variant,is_classified|unique:product_stocks,sku,' . $product->stock()->first()->id,
                ]);

                if ($sku_validator->fails()) {
                    DB::rollBack();
                    return back()->withInput()->withErrors($sku_validator);
                }
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors($validator);
        }

        if (isDemoServer()):
            Toastr::info(__('This function is disabled in demo server.'));
            return redirect()->back();
        endif;

        DB::beginTransaction();
        try {
            $this->products->update($request);
            Toastr::success(__('Updated Successfully'));
            session()->forget('attributes');
            DB::commit();
            return redirect($request->r);
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return back();
        }
    }


    public function variants(Request $request)
    {
        if ($request->has_variant == 1):
            $variants = array();

            $product_price = $request->price;
            $colors       = false;

            if (!empty($request->colors)):
                array_push($variants, $request->colors);
                $colors = true;
            endif;

            if ($request->has('attribute_sets')):
                foreach ($request->attribute_sets as $key => $attribute_set):
                    $attribute_values = 'attribute_values_'.$attribute_set;
                    $values = array();
                    if ($request->has($attribute_values)):
                        foreach ($request[$attribute_values] as $value):
                            array_push($values, $value);
                        endforeach;
                    endif;
                    if($request->has($attribute_values)):
                        array_push($variants, $values);
                    endif;
                endforeach;
            endif;
            $variants_data = VariantUtility::getVariants($variants);
            if (!empty($variants_data[0])):
                return view('admin.products.products.variants', compact('variants','variants_data','product_price','colors'));
            else:
                return view('admin.products.products.variants');
            endif;
        else:
            return '';
        endif;
    }


    public function variantsEdit(Request $request)
    {
        $product = $this->products->get($request->id);

        if ($request->has_variant == 1):
            $variants = array();

            $product_price = $request->price;
            $colors       = false;

            if (!empty($request->colors)):
                array_push($variants, $request->colors);
                $colors = true;
            endif;

            if ($request->has('attribute_sets')):
                foreach ($request->attribute_sets as $key => $attribute_set):
                    $attribute_values = 'attribute_values_'.$attribute_set;
                    $values = array();
                    if ($request->has($attribute_values)):
                        foreach ($request[$attribute_values] as $value):
                            array_push($values, $value);
                        endforeach;
                    endif;
                    if($request->has($attribute_values)):
                        array_push($variants, $values);
                    endif;
                endforeach;
            endif;
            $variants_data = VariantUtility::getVariants($variants);
            if (!empty($variants_data[0])):
                return view('admin.products.products.variants_edit', compact('variants','variants_data','product_price','product','colors'));
            else:
                return view('admin.products.products.variants_edit');
            endif;
        else:
            return '';
        endif;
    }

    public function getAttributeValues(Request $request)
    {
        $attributes_sets = $request->attribute_sets;

        if (!empty($attributes_sets)):
            $attributes = $this->attributes->all()->whereIn('attributes.id', $attributes_sets)->where('lang','en')->get();
            return view('admin.products.products.values', compact('attributes','request','attributes_sets'));
        else:
            return '';
        endif;
    }

    public function restore($id){

        DB::beginTransaction();
        try {
            $this->products->restore($id);
            Toastr::success(__('Updated successfully as unpublished'));
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return back();
        }
    }
    public function productStatusChange($status,$id){
        if (isDemoServer()):
            Toastr::info(__('This function is disabled in demo server.'));
            return redirect()->back();
        endif;
        DB::beginTransaction();
        try {
            $this->products->productStatusChange($status,$id);
            Toastr::success(__('Updated successfully as '.$status));
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function cloneProduct(Request $request, $id){
        try {
            $languages  = $this->languages->all()->orderBy('id', 'asc')->get();

            $lang       = $request->lang != '' ? $request->lang : \App::getLocale();
            if ($this->products->get($id) && $product_language = $this->products->getByLang($id, $lang)):
                $categories     = $this->categories->allCategory()->where('parent_id', null)->where('status',1);
                $brands         = $this->brands->all()->where('lang','en')->where('status',1)->get();
                $colors         = $this->colors->all()->where('lang', 'en')->get();
                $attributes     = $this->attributes->all()->where('lang', 'en')->get();
                $r              = $request->r != ''? $request->r : $request->server('HTTP_REFERER');
                $clone          = 1;
                $campaigns      = \App\Models\Campaign::where('status', 1)->where('end_date','>',Carbon::now()->format('Y-m-d'))->get();
                if($product_language->product->is_wholesale != 1):
                    return view('admin.products.products.edit',
                        compact(
                            'languages',
                            'lang',
                            'product_language',
                            'categories','brands',
                            'attributes',
                            'colors',
                            'r',
                            'clone',
                            'campaigns'
                        ));
                else:
                    return redirect()->route('wholesale.product.clone', [$id]);
                endif;

            else:
                Toastr::error(__('Not found'));
                return back();
            endif;
        } catch (\Exception $e){
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function StoreCloneProduct(ProductStoreRequest $request){
        if (isDemoServer()):
            Toastr::info(__('This function is disabled in demo server.'));
            return redirect()->back();
        endif;

        DB::beginTransaction();
        try {
            $this->products->store($request);
            Toastr::success(__('Created Successfully'));
            DB::commit();
            return redirect($request->r);
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function reviews(ReviewInterface $review,Request $request)
    {
        $reviews = $review->paginateReviews($request->all());
        return view('admin.products.products.review',compact('reviews'));
    }

    public function replies($review_id)
    {
        $replies = ReviewReply::where('review_id',$review_id)->paginate(15);
        return view('admin.products.products.replies',compact('replies'));
    }


    public function reviewStatusChange(ReviewInterface $review,Request $request)
    {
        if (isDemoServer()):
            $response['message']    = __('This function is disabled in demo server.');
            $response['title']      = __('Ops..!');
            $response['status']     = 'error';
            return response()->json($response);
        endif;

        DB::beginTransaction();
        try {
            $review->statusChange($request['data']);
            $response['message'] = __('Updated Successfully');
            $response['title'] = __('Success');
            $response['status'] = 'success';
            DB::commit();
            return response()->json($response);
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function productImport(Request $request)
    {
        return view('admin.products.products.import-products');
    }
}
