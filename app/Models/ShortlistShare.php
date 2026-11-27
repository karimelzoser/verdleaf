<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ShortlistShare extends Model {
 protected $fillable=['token_hash','items','expires_at','revoked_at'];
 protected function casts():array{return ['items'=>'array','expires_at'=>'datetime','revoked_at'=>'datetime'];}
}