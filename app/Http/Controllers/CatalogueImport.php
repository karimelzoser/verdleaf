<?php
namespace App\Http\Controllers;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB,Validator};
use Illuminate\Support\Str;
class CatalogueImport{
 const FIELDS=['name','category','name_ar','botanical_name','description','description_ar','origin','source_url'];
 private function admin(Request $r){abort_unless($r->user()?->role==='admin',403);}
 public function index(Request $r){$this->admin($r);return view('admin.import',['rows'=>$r->session()->get('catalogue_import',[])]);}
 public function preview(Request $r){
  $this->admin($r);$r->validate(['file'=>'required|file|mimes:csv,txt|max:1024']);
  $handle=fopen($r->file('file')->getRealPath(),'rb');$rows=[];$errors=[];$seen=[];
  try{
   $header=fgetcsv($handle,0,',','"','');$header=array_map(fn($s)=>trim((string)$s),$header?:[]);if(isset($header[0]))$header[0]=preg_replace('/^\xEF\xBB\xBF/','',$header[0]);
   if($header!==self::FIELDS)return back()->withErrors(['file'=>'Use the exact template header.']);
   $i=1;while(($line=fgetcsv($handle,0,',','"',''))!==false){
    $i++;if($i>501){$errors[]='Maximum 500 rows.';break;}
    if($line===[null])continue;if(count($line)!==count(self::FIELDS)){$errors[]="Row $i: wrong column count.";continue;}
    $d=array_combine(self::FIELDS,array_map(fn($x)=>trim((string)$x),$line));
    $v=Validator::make($d,['name'=>'required|string|max:160','category'=>'required|string|max:120','name_ar'=>'nullable|string|max:160','botanical_name'=>'nullable|string|max:160','description'=>'nullable|string|max:10000','description_ar'=>'nullable|string|max:10000','origin'=>'nullable|string|max:200','source_url'=>'nullable|url:http,https|max:1000']);
    $slug=Str::slug($d['name']);if($v->fails()||!$slug||isset($seen[$slug])){$errors[]="Row $i: invalid fields or duplicate name.";continue;}
    $seen[$slug]=true;
    if(Product::where('slug',$slug)->where('status','published')->exists()){$errors[]="Row $i: published product must be edited through approval workflow.";continue;}
    $rows[]=$d+['slug'=>$slug];
   }
  }finally{fclose($handle);}
  $r->session()->forget('catalogue_import');if($errors)return back()->withErrors(['file'=>implode(' ',array_slice($errors,0,20))]);if(!$rows)return back()->withErrors(['file'=>'No data rows.']);
  $r->session()->put('catalogue_import',$rows);return back()->with('message','Dry run passed; no products changed.');
 }
 public function commit(Request $r){
  $this->admin($r);$r->validate(['confirmed'=>'accepted']);$rows=$r->session()->get('catalogue_import',[]);abort_unless($rows,422);
  DB::transaction(function()use($rows){foreach($rows as $row){$current=Product::where('slug',$row['slug'])->lockForUpdate()->first();abort_if($current&&$current->status==='published',409,'A product has been published since preview. Run the dry run again.');$row['status']='draft';if($current)$current->update($row);else Product::create($row);}});
  $r->session()->forget('catalogue_import');return back()->with('message',count($rows).' draft rows saved.');
 }
 public function export(Request $r){
  $this->admin($r);
  return response()->streamDownload(function(){$out=fopen('php://output','w');fputcsv($out,self::FIELDS,',','"','');Product::orderBy('id')->chunkById(100,function($products)use($out){foreach($products as $p){$line=[];foreach(self::FIELDS as $key){$s=(string)$p->$key;if(preg_match('/^[=+\-@\t\r]/',$s))$s="'".$s;$line[]=$s;}fputcsv($out,$line,',','"','');}});fclose($out);},'verdleaf-catalogue.csv',['Content-Type'=>'text/csv','Cache-Control'=>'private, no-store']);
 }
}