<?php
namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\ContentEntry;
use Illuminate\Http\Request;
class PublicSite {
 public const SECTIONS=[
 'about'=>['Our story','The people and agricultural heritage behind Verdleaf.','story'],
 'inside-verdleaf'=>['Inside Verdleaf','Follow an ingredient from the field to export preparation.','tour'],
 'farms'=>['Where it grows','Explore agricultural origins and cultivation practices.','farm'],
 'facilities'=>['From crop to ingredient','Discover processing and handling information.','facility'],
 'quality'=>['Quality, in detail','Review the evidence before you source.','quality'],
 'certifications'=>['Certificates & documents','Find approved certification scope and validity information.','certificate'],
 'crop-calendar'=>['Nature has its own calendar','Planting and harvesting information, by crop and region.','crop'],
 'exhibitions'=>['Meet us in person','Upcoming and past exhibitions.','event'],
 'visits'=>['Growing relationships','Business visits and delegations.','visit'],
 'markets'=>['Beyond borders','Verified export markets and commercial information.','market'],
 'industries'=>['Ingredients for your production','Explore the applications relevant to your sourcing project.','industry'],
 'export-services'=>['Prepared for the journey','Packaging, documentation and export options.','export'],
 'responsible-sourcing'=>['Responsible sourcing','Documented agricultural and sourcing practices.','sourcing'],
 'insights'=>['Field notes','Practical reading for ingredient buyers.','article'],
 'downloads'=>['Buyer resources','Approved commercial and technical documents.','document'],
 'how-to-order'=>['How to source','Choose an ingredient, specify your requirements and request a quotation.','guide'],
 'contact'=>['Start a conversation','Tell the export team what your business needs.','contact'],
 'privacy'=>['Privacy information','Review the approved data handling information before submitting a request.','privacy'],
 ];
 public function home(){return view('home',['hero'=>ContentEntry::visible()->where('type','hero')->with('media')->latest('id')->first(),'featured'=>Product::visible()->whereIn('slug',['chamomile','hibiscus','fennel'])->get(),'news'=>ContentEntry::visible()->with('media')->whereIn('type',['article','event','visit'])->latest('event_date')->take(3)->get()]);}
 public function catalogue(Request $r,$mode='catalogue'){
  $q=trim(mb_substr((string)$r->query('q',''),0,100));$category=trim(mb_substr((string)$r->query('category',''),0,100));$form=trim(mb_substr((string)$r->query('form',''),0,100));$application=trim(mb_substr((string)$r->query('application',''),0,100));
  $builder=Product::visible()->with(['variants'=>fn($v)=>$v->visible()]);
  if($q!==''){$ids=Product::visible()->select(['id','name','name_ar','botanical_name','aliases'])->get()->filter(fn($p)=>\App\Services\IngredientSearch::matches($p,$q))->pluck('id');$builder->whereIn('id',$ids);}
  if($category!=='')$builder->where('category',$category);
  if($form!=='')$builder->whereHas('variants',fn($v)=>$v->visible()->where('form',$form));
  if($application!=='')$builder->whereJsonContains('applications',$application);
  return view('catalogue',['products'=>$builder->orderBy('name')->paginate(12)->withQueryString(),'categories'=>Product::visible()->distinct()->orderBy('category')->pluck('category'),'mode'=>$mode,'q'=>$q,'category'=>$category,'form'=>$form,'application'=>$application]);
 }
 public function printCatalogue(Request $r){$q=trim(mb_substr((string)$r->query('q',''),0,100));$category=mb_substr((string)$r->query('category',''),0,100);$form=mb_substr((string)$r->query('form',''),0,100);$application=mb_substr((string)$r->query('application',''),0,100);$b=Product::visible()->with(['variants'=>fn($v)=>$v->visible()]);if($category!=='')$b->where('category',$category);if($form!=='')$b->whereHas('variants',fn($v)=>$v->visible()->where('form',$form));if($application!=='')$b->whereJsonContains('applications',$application);$products=$b->orderBy('name')->get()->filter(fn($p)=>\App\Services\IngredientSearch::matches($p,$q));return view('print-catalogue',compact('products'));}
 public function product(string $slug){$product=Product::visible()->where('slug',$slug)->with(['media','variants'=>fn($q)=>$q->visible()->with('media')->orderBy('form')])->firstOrFail();$periods=\App\Models\CropPeriod::where('product_id',$product->id)->where('status','published')->orderBy('crop_year')->orderBy('region')->orderBy('month')->get();return view('product',compact('product','periods'));}
 public function section(string $section){$meta=self::SECTIONS[$section];$entries=ContentEntry::previewVisible()->where('type',$meta[2])->with('media')->latest('event_date')->latest('id')->get();return view('section',compact('section','meta','entries'));}
 public function entry(string $section,string $slug){$meta=self::SECTIONS[$section];$entry=ContentEntry::previewVisible()->where('type',$meta[2])->where('slug',$slug)->with('media')->firstOrFail();return view('entry',compact('section','meta','entry'));}
 public function robots(){return response(config('verdleaf.preview')?"User-agent: *\nDisallow: /\n":"User-agent: *\nDisallow: /admin\nDisallow: /buyer-desk\nDisallow: /compare\nSitemap: ".url('/sitemap.xml')."\n",200,['Content-Type'=>'text/plain']);}
 public function sitemap(){abort_if(config('verdleaf.preview'),404);$urls=[url('/'),url('/products'),url('/ar'),url('/ar/products')];foreach(Product::where('status','published')->whereNotNull('name_ar')->whereNotNull('description_ar')->pluck('slug') as $slug)$urls[]=route('ar.product',$slug);foreach(Product::where('status','published')->pluck('slug') as $slug)$urls[]=route('product',$slug);foreach(self::SECTIONS as $slug=>$meta)if(ContentEntry::visible()->where('type',$meta[2])->exists())$urls[]=route('section',$slug);foreach(['exhibitions','visits','insights','certifications','markets','industries','farms','facilities','responsible-sourcing','downloads'] as $section)foreach(ContentEntry::visible()->where('type',self::SECTIONS[$section][2])->pluck('slug') as $slug)$urls[]=route('entry',[$section,$slug]);return response()->view('sitemap',compact('urls'),200,['Content-Type'=>'application/xml']);}
}
