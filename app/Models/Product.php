<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
class Product extends Model {
    protected $fillable=['slug','name','name_ar','botanical_name','category','description','description_ar','origin','applications','aliases','status','source_url','reviewed_at'];
    protected function casts():array{return ['applications'=>'array','aliases'=>'array','reviewed_at'=>'datetime'];}
    public function variants(){return $this->hasMany(Variant::class);}
    public function scopeVisible(Builder $query):Builder {return config('verdleaf.preview') ? $query->whereIn('status',['draft','published']) : $query->where('status','published');}
}
