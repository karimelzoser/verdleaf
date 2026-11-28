<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SourcingItem extends Model {
    protected $fillable=['variant_id','product_name','form','grade','quantity_kg','purposes'];
    protected function casts():array{return ['purposes'=>'array','quantity_kg'=>'decimal:3'];}
}
