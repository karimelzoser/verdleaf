<?php
namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\ContentEntry;
use Illuminate\Http\Request;
class ArabicSite {
 const SECTIONS=['about'=>'عن الشركة','farms'=>'المزارع','facilities'=>'التجهيز والتصنيع','quality'=>'الجودة','certifications'=>'الشهادات','exhibitions'=>'المعارض','visits'=>'الزيارات','responsible-sourcing'=>'التوريد المسؤول','contact'=>'تواصل معنا','insights'=>'أخبار ومقالات'];
 public function home(){return view('ar.home',['featured'=>Product::visible()->whereNotNull('name_ar')->whereNotNull('description_ar')->take(3)->get()]);}
 public function catalogue(Request $r){$q=trim(mb_substr((string)$r->query('q',''),0,100));$products=Product::visible()->whereNotNull('name_ar')->whereNotNull('description_ar');if($q!==''){$ids=Product::visible()->select(['id','name','name_ar','botanical_name','aliases'])->get()->filter(fn($p)=>\App\Services\IngredientSearch::matches($p,$q))->pluck('id');$products->whereIn('id',$ids);}return view('ar.catalogue',['products'=>$products->orderBy('name_ar')->paginate(12)->withQueryString(),'q'=>$q]);}
 public function product(string $slug){$p=Product::visible()->where('slug',$slug)->whereNotNull('name_ar')->whereNotNull('description_ar')->firstOrFail();return view('ar.product',['p'=>$p]);}
 public function section(string $section){abort_unless(isset(self::SECTIONS[$section]),404);$entries=ContentEntry::visible()->where('type',PublicSite::SECTIONS[$section][2])->whereNotNull('title_ar')->whereNotNull('body_ar')->latest('event_date')->get();return view('ar.section',['section'=>$section,'title'=>self::SECTIONS[$section],'entries'=>$entries]);}
}
