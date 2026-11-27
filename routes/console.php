<?php
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
Artisan::command('verdleaf:admin {email}', function(){
 $email=$this->argument('email');$password=$this->secret('Set a unique password (at least 14 characters)');
 $data=Validator::make(['email'=>$email,'password'=>$password],['email'=>'required|email|max:255|unique:users,email','password'=>'required|string|min:14|max:128'])->validate();
 User::create(['name'=>'Administrator','email'=>$data['email'],'password'=>$data['password'],'role'=>'admin']);
 $this->info('Administrator created.');
});

Artisan::command('verdleaf:notify',function(){app(\App\Services\SendNotifications::class)->run();});
\Illuminate\Support\Facades\Schedule::command('verdleaf:notify')->everyFiveMinutes()->withoutOverlapping();

Artisan::command('verdleaf:staff {email} {role}',function(){
 $role=$this->argument('role');$email=$this->argument('email');$password=$this->secret('Set a unique password (at least 14 characters)');
 $d=Validator::make(compact('role','email','password'),['role'=>'required|in:editor,quality,sales,analytics','email'=>'required|email|max:255|unique:users,email','password'=>'required|string|min:14|max:128'])->validate();
 User::create(['name'=>ucfirst($role),'email'=>$email,'password'=>$password,'role'=>$role]);$this->info('Staff account created.');
});
