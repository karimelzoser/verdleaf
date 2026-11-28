<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
class User extends Authenticatable {
    protected $fillable = ['name','email','password','role'];
    protected $hidden = ['password','remember_token','totp_secret','recovery_hashes','last_totp_step','session_version'];
    protected function casts(): array {return ['password'=>'hashed','totp_secret'=>'encrypted','recovery_hashes'=>'encrypted:array','last_totp_step'=>'integer','session_version'=>'integer','disabled_at'=>'datetime'];}
}
