<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CropPeriod extends Model {
 protected $fillable=['product_id','region','crop_year','month','stage','source_url','status'];
 public function product(){return $this->belongsTo(Product::class);}
}
