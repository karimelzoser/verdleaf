<?php
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
Artisan::command('verdleaf:admin {email}', function(){
 $email=mb_strtolower($this->argument('email'));$password=$this->secret('Set a unique password (at least 14 characters)');
 $data=Validator::make(['email'=>$email,'password'=>$password],['email'=>'required|email|max:255|unique:users,email','password'=>'required|string|min:14|max:128'])->validate();
 User::create(['name'=>'Administrator','email'=>$data['email'],'password'=>$data['password'],'role'=>'admin']);
 $this->info('Administrator created.');
});

Artisan::command('verdleaf:notify',function(){app(\App\Services\SendNotifications::class)->run();});
\Illuminate\Support\Facades\Schedule::command('verdleaf:notify')->everyFiveMinutes()->withoutOverlapping();

Artisan::command('verdleaf:staff {email} {role}',function(){
 $role=$this->argument('role');$email=mb_strtolower($this->argument('email'));$password=$this->secret('Set a unique password (at least 14 characters)');
 $d=Validator::make(compact('role','email','password'),['role'=>'required|in:editor,quality,sales,analytics','email'=>'required|email|max:255|unique:users,email','password'=>'required|string|min:14|max:128'])->validate();
 User::create(['name'=>ucfirst($role),'email'=>$email,'password'=>$password,'role'=>$role]);$this->info('Staff account created.');
});

Artisan::command('verdleaf:mfa-enroll {email}',function(){
 $u=User::where('email',mb_strtolower($this->argument('email')))->firstOrFail();
 if(!$this->confirm('Reset MFA and revoke all existing sessions for this account?'))return;
 $secret=\App\Services\StaffSecurity::generateSecret();$codes=[];for($i=0;$i<8;$i++)$codes[]=bin2hex(random_bytes(12));
 $u->forceFill(['totp_secret'=>$secret,'recovery_hashes'=>array_map(fn($c)=>\Illuminate\Support\Facades\Hash::make($c),$codes),'last_totp_step'=>null,'session_version'=>$u->session_version+1])->save();
 \App\Services\StaffSecurity::event($u->id,'mfa_enrolled');
 $this->warn('Private terminal only. Configure a time-based authenticator (SHA1, 6 digits, 30 seconds). Never share or log this secret.');
 $this->line('Secret: '.$secret);$this->line('Single-use recovery codes:');foreach($codes as $c)$this->line($c);
});
Artisan::command('verdleaf:revoke {email} {--disable}',function(){
 $u=User::where('email',mb_strtolower($this->argument('email')))->firstOrFail();
 $u->forceFill(['session_version'=>$u->session_version+1,'disabled_at'=>$this->option('disable')?now():$u->disabled_at])->save();
 \App\Services\StaffSecurity::event($u->id,$this->option('disable')?'account_disabled':'sessions_revoked');$this->info('Staff access updated.');
});
