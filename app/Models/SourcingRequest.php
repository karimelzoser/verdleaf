<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SourcingRequest extends Model {
    protected $fillable=['destination_port','incoterm','delivery_date','packaging_preference','attachment_path','attachment_name','reference','idempotency_key','company','email','contact_name','country','notes','status','consent_at'];
    protected function casts():array{return ['consent_at'=>'datetime','delivery_date'=>'date'];}
    public function items(){return $this->hasMany(SourcingItem::class);}
}
