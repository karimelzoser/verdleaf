<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
class Metrics{
 public static function increment(string $event):void{
  if(config('verdleaf.preview'))return;
  $key=['day'=>now()->toDateString(),'event'=>$event];
  DB::table('daily_metrics')->insertOrIgnore($key+['count'=>0]);
  DB::table('daily_metrics')->where($key)->increment('count');
 }
}