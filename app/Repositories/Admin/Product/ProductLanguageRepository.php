<?php

namespace App\Repositories\Admin\Product;

use DB;
use App\Traits\ImageTrait;
use App\Models\ProductLanguage;
use Illuminate\Support\Facades\Log;
use function PHPUnit\Framework\isEmpty;
use Stichoza\GoogleTranslate\GoogleTranslate;
use App\Repositories\Interfaces\Admin\Product\ProductLanguageInterface;

class ProductLanguageRepository implements ProductLanguageInterface
{
    use ImageTrait;

    public function get($id)
    {
        return ProductLanguage::find($id);
    }
    public function getByLang($id, $request)
    {
        return ProductLanguage::where('product_id', $id)->where('lang', $request->lang);
    }

    public function all()
    {
        return ProductLanguage::latest();
    }



    public function store($request)
    {
        DB::beginTransaction();
        try {
            if ($request->product_lang_id != ''):
                $product_lang                      = $this->get($request->product_lang_id);
            else:
                $product_lang                      = new  ProductLanguage();
            endif;
            $product_lang->name                = $request->name;
            $product_lang->product_id          = $request->product_id;
            $product_lang->lang                = $request->lang;
            $product_lang->short_description   = $request->short_description;
            $product_lang->description         = $request->description;
            $product_lang->tags                = $request->tags;
            $product_lang->unit                = $request->unit;
            $product_lang->meta_title          = $request->meta_title;
            $product_lang->meta_description    = $request->meta_description;
            $product_lang->meta_keywords       = $request->meta_keywords;
            if ($request->pdf_specification != ''):
                $file   = $this->getFile($request->pdf_specification);
                if ($file):
                    $product_lang->pdf_specification        = $file;
                    $product_lang->pdf_specification_id     = $request->pdf_specification;
                else:
                    $product_lang->pdf_specification        = [];
                endif;
            else:
                $product_lang->pdf_specification        = [];
                $product_lang->pdf_specification_id     = null;
            endif;
            $product_lang->save();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            return false;
        }
    }
    // public function translateText($text, $targetLanguage)
    // {
    //     $translator = new GoogleTranslate();
    //     $translator->setSource('tr');
    //     $translator->setTarget($targetLanguage); // Target language: English or Georgian
    //     return $translator->translate($text);
    // }

    // public function store($request)
    // {
    //     Log::info('Product Language Data:', ['data' => $request->all()]);
    
    //     DB::beginTransaction();
    //     try {
    //         // Always translate to both `en` and `ka`
    //         $translateToLanguages = ['ka', 'en'];
    
    //         // Format description as HTML
    //         $formattedShortDescription = "<p>" . htmlspecialchars($request['short_description']) . "</p>";
    
    //         foreach ($translateToLanguages as $language) {
    //             $product_lang = $this->getOrCreateProductLang($request, $language);
    
    //             // Translate only if the language is different from the original
    //             if ($language === 'ka') {
    //                 $product_lang->name = $request['name'];
    //                 $product_lang->description = $formattedShortDescription;
    //             } else {
    //                 $product_lang->name = $this->translateText($request['name'], 'en', 'ka');
    //                 $product_lang->description = $this->translateText($formattedShortDescription, 'en', 'ka');
    //             }
    
    //             $product_lang->product_id = $request['product_id'];
    //             $product_lang->lang = $language;
    //             $product_lang->short_description = 'Description';
    //             $product_lang->tags = $request['tags'];
    //             $product_lang->unit = $request['unit'];
    //             $product_lang->meta_title = $request['meta_title'];
    //             $product_lang->meta_description = $request['meta_description'];
    //             $product_lang->meta_keywords = $request['meta_keywords'];
    
    //             $product_lang->save();
    //         }
    
    //         DB::commit();
    //         return true;
    //     } catch (\Exception $e) {
    //         DB::rollback();
    //         Log::error('Error saving product language:', ['error' => $e->getMessage()]);
    //         return false;
    //     }
    // }
    

    protected function getOrCreateProductLang($request, $language)
    {
        $product_lang = ProductLanguage::where('product_id', $request['product_id'])
            ->where('lang', $language)
            ->first();

        return $product_lang ?? new ProductLanguage();
    }

    public function translateText($text, $targetLanguage, $sourceLanguage)
    {
        if (empty($text)) return '';
    
        $translator = new GoogleTranslate();
    
        $translator->setSource($sourceLanguage);
        $translator->setTarget($targetLanguage);
    
        return $translator->translate($text);
    }
    




}
