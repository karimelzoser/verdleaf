<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SourcingRequest extends Model {
    protected $fillable=['reference','idempotency_key','company','email','contact_name','country','notes','status','consent_at'];
    protected function casts():array{return ['consent_at'=>'datetime'];}
    public function items(){return $this->hasMany(SourcingItem::class);}
}
