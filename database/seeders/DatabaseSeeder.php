<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Support\Str;
class DatabaseSeeder extends Seeder {
 public function run():void {
  // Historic public catalogue inventory. All are drafts until client verification.
  $drafts=collect(json_decode(file_get_contents(database_path('data/catalogue-drafts.json')),true,512,JSON_THROW_ON_ERROR)['products'])->keyBy('name');
  $groups=[
   'Herbs & leaves'=>['Basil','Celery','Dill','Lemongrass','Marjoram','Molokhia','Parsley','Peppermint','Rosemary','Sage','Spearmint','Thyme','Moringa'],
   'Flowers & botanicals'=>['Chamomile','Calendula','Hibiscus'],
   'Seeds & spices'=>['Black seeds (Nigella)','Caraway','Coriander','Fennel','Fenugreek','Sesame','Anise'],
   'Dehydrated produce'=>['Dried beetroot','Dried garlic','Dried onion','Dried lemon'],
   'Roots & resins'=>['Liquorice','Frankincense'],
  ];
  $forms=['Chamomile'=>['Whole flowers','TBC','Powder'],'Hibiscus'=>['Whole calyces','Crushed','TBC','Powder'],'Fennel'=>['Whole seeds','Ground'],'Dried lemon'=>['Dried whole','Mechanically dried']];
  foreach($groups as $category=>$names)foreach($names as $name){
   $p=Product::firstOrCreate(['slug'=>Str::slug($name)],array_merge($drafts->get($name,[]),['name'=>$name,'category'=>$category,'status'=>'draft','source_url'=>'https://verdleaf.com/products/']));
   foreach($forms[$name]??[] as $form)Variant::firstOrCreate(['sku'=>strtoupper(Str::slug($name.'-'.$form))],['product_id'=>$p->id,'form'=>$form,'status'=>'draft']);
  }
  $this->call(CompanyDraftSeeder::class);
 }
}
