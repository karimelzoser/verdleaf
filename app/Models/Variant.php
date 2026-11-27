<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Variant extends Model {
    protected $fillable=['product_id','sku','form','grade','description','specifications','packaging','status'];
    protected function casts():array{return ['specifications'=>'array','packaging'=>'array'];}
    public function product(){return $this->belongsTo(Product::class);}
    public function scopeVisible($query){return config('verdleaf.preview')?$query->whereIn('status',['draft','published']):$query->where('status','published');}
}
