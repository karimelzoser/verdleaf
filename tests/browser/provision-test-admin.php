<?php
// Used only by browser CI on an isolated preview database. Never use in production.
require __DIR__.'/../../vendor/autoload.php';
$app=require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if(!app()->environment('local','testing')||!config('verdleaf.preview')||config('database.default')!=='sqlite'||!str_ends_with(config('database.connections.sqlite.database'),'browser.sqlite'))exit(1);
$email=$argv[1]??'';
if(!preg_match('/^browser-[a-z]+@example\.test$/D',$email))exit(1);
$u=App\Models\User::firstOrCreate(['email'=>$email],['name'=>'Synthetic browser administrator','password'=>'browser-test-only-password','role'=>'admin']);
$secret=App\Services\StaffSecurity::generateSecret();
$code=bin2hex(random_bytes(12));
$u->forceFill(['totp_secret'=>$secret,'recovery_hashes'=>[Illuminate\Support\Facades\Hash::make($code)],'last_totp_step'=>null])->save();
echo $code.PHP_EOL;
