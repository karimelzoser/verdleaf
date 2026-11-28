<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\{Product,Variant};
use Illuminate\Support\Str;
class ExpansionCandidateSeeder extends Seeder{
 public function run():void{
  $data=json_decode(file_get_contents(database_path('data/expansion-candidates.json')),true,512,JSON_THROW_ON_ERROR);
  foreach($data['products'] as $record){
   $p=Product::firstOrCreate(['slug'=>Str::slug($record['name'])],['name'=>$record['name'],'name_ar'=>$record['name_ar'],'category'=>$record['category'],'status'=>'draft']);
   if($p->status!=='draft')continue;
   foreach($record['forms'] as $form)Variant::firstOrCreate(['sku'=>'CANDIDATE-'.strtoupper(Str::slug($record['name'].'-'.$form))],['product_id'=>$p->id,'form'=>$form,'status'=>'draft']);
  }
 }
}