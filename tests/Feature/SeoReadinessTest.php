<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{Product,ContentEntry};
class SeoReadinessTest extends TestCase{
 use RefreshDatabase;
 public function test_approved_translations_and_canonical_pagination():void{
  config(['verdleaf.preview'=>false]);
  $p=Product::create(['name'=>'Test ingredient','name_ar'=>'منتج للاختبار','description'=>'Reference ingredient','description_ar'=>'مكون مرجعي','category'=>'Herbs & leaves','slug'=>'seo-test','status'=>'published']);
  Product::create(['name'=>'Unapproved ingredient','slug'=>'seo-hidden','category'=>'Herbs & leaves','status'=>'draft']);
  Product::create(['name'=>'Untranslated ingredient','slug'=>'seo-empty-ar','category'=>'Herbs & leaves','status'=>'published','name_ar'=>'','description_ar'=>'']);
  ContentEntry::create(['type'=>'story','slug'=>'seo-story','title'=>'Approved story','body'=>'Approved test copy','title_ar'=>'قصة','body_ar'=>'محتوى الاختبار','status'=>'published']);
  $this->get('/sitemap.xml')->assertOk()->assertSee('/products/seo-test')->assertSee('/ar/products/seo-test')->assertSee('/ar/about')->assertDontSee('seo-hidden')->assertDontSee('/ar/products/seo-empty-ar');
  $this->get('/about')->assertOk()->assertSee('hreflang="ar"',false)->assertSee('hreflang="x-default"',false);
  $this->get('/ar/about')->assertOk()->assertSee('hreflang="en"',false);
  $this->get('/products?page=2')->assertOk()->assertSee('rel="canonical" href="'.url('/products').'?page=2"',false);
  $this->get('/products?q=Test')->assertOk()->assertHeader('X-Robots-Tag','noindex, follow')->assertDontSee('hreflang="ar"',false);
  $this->get('/ar/products/seo-empty-ar')->assertNotFound();
  $this->get('/ar/visits')->assertOk()->assertSee('noindex,follow',false);
  config(['verdleaf.preview'=>true]);$this->get('/sitemap.xml')->assertNotFound();
 }
}