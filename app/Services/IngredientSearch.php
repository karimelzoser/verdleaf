<?php
namespace App\Services;
class IngredientSearch {
 public static function normalize(?string $value):string{
  $s=mb_strtolower($value??'','UTF-8');
  $s=preg_replace('/[\x{064B}-\x{065F}\x{0670}\x{0640}]/u','',$s);
  $s=strtr($s,['أ'=>'ا','إ'=>'ا','آ'=>'ا','ى'=>'ي','ة'=>'ه']);
  return trim(preg_replace('/[^\p{L}\p{N}]+/u',' ',$s));
 }
 public static function matches($p,string $query):bool{
  $q=self::normalize($query);if($q==='')return true;
  $haystack=array_map([self::class,'normalize'],array_merge([$p->name,$p->name_ar,$p->botanical_name],$p->aliases??[]));
  foreach($haystack as $candidate){
   if(str_contains($candidate,$q))return true;
   if(strlen($q)>=4&&preg_match('/^[a-z ]+$/D',$q)&&preg_match('/^[a-z ]+$/D',$candidate)){
    foreach(explode(' ',$candidate) as $word)if(levenshtein($q,$word)<=1)return true;
   }
  }return false;
 }
}