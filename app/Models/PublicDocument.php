<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PublicDocument extends Model{
 protected $fillable=['path','title','kind','revision','status','expires_at'];
 protected function casts():array{return ['expires_at'=>'date'];}
}