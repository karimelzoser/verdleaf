<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class GeneralEnquiry extends Model {
 protected $fillable=['reference','type','company','name','email','country','message','requested_date','status','consent_at'];
 protected function casts():array{return ['requested_date'=>'date','consent_at'=>'datetime'];}
}