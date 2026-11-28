<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CommercialCase extends Model{
 protected $fillable=['sourcing_request_id','created_by','reference','kind','status','title','details','currency','total_minor','due_at'];
 protected function casts():array{return ['details'=>'array','total_minor'=>'integer','due_at'=>'date'];}
 public function sourcingRequest(){return $this->belongsTo(SourcingRequest::class);}
}