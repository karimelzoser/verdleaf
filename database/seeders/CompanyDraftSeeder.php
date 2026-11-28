<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\{ContentEntry,UrlRedirect};
class CompanyDraftSeeder extends Seeder{
 public function run():void{
  $data=json_decode(file_get_contents(database_path('data/company-drafts.json')),true,512,JSON_THROW_ON_ERROR);
  foreach($data['entries'] as $entry)ContentEntry::firstOrCreate(['type'=>$entry['type'],'slug'=>$entry['slug']],$entry+['status'=>'draft']);
  foreach(['/about-us'=>'/about','/contact-us'=>'/contact'] as $old=>$new)UrlRedirect::firstOrCreate(['old_path'=>$old],['new_path'=>$new]);
 }
}