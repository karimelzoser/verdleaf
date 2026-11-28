<?php
namespace App\Services;
use App\Models\User;
use Illuminate\Support\Facades\{DB,Hash};
class StaffSecurity {
 const ALPHABET='ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
 public static function required():bool{return app()->environment('production')||config('verdleaf.staff_mfa');}
 public static function event(?int $id,string $event):void{DB::table('security_events')->insert(['user_id'=>$id,'event'=>$event,'created_at'=>now()]);}
 public static function generateSecret():string{
  $bits='';foreach(str_split(random_bytes(20)) as $b)$bits.=str_pad(decbin(ord($b)),8,'0',STR_PAD_LEFT);
  return implode('',array_map(fn($v)=>self::ALPHABET[bindec($v)],str_split($bits,5)));
 }
 public static function code(string $secret,int $step):string{
  $bits='';foreach(str_split($secret) as $c){$i=strpos(self::ALPHABET,$c);if($i===false)throw new \InvalidArgumentException('Invalid secret');$bits.=str_pad(decbin($i),5,'0',STR_PAD_LEFT);}
  $key='';foreach(str_split($bits,8) as $b)if(strlen($b)===8)$key.=chr(bindec($b));
  $h=hash_hmac('sha1',pack('N2',intdiv($step,4294967296),$step%4294967296),$key,true);
  $o=ord($h[19])&15;$n=unpack('N',substr($h,$o,4))[1]&0x7fffffff;
  return str_pad((string)($n%1000000),6,'0',STR_PAD_LEFT);
 }
 public static function consume(int $id,string $code):bool{
  return DB::transaction(function()use($id,$code){
   $u=User::lockForUpdate()->find($id);if(!$u||$u->disabled_at||!$u->totp_secret)return false;
   if(preg_match('/^\d{6}$/D',$code)){
    $step=intdiv(time(),30);foreach([$step,$step-1,$step+1] as $s){if($s<=($u->last_totp_step??-1))continue;if(hash_equals(self::code($u->totp_secret,$s),$code)){$u->forceFill(['last_totp_step'=>$s])->save();return true;}}
   }else{
    $hashes=$u->recovery_hashes??[];foreach($hashes as $i=>$hash)if(Hash::check($code,$hash)){unset($hashes[$i]);$u->forceFill(['recovery_hashes'=>array_values($hashes)])->save();return true;}
   }return false;
  });
 }
}