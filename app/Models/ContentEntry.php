<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ContentEntry extends Model {
    protected $fillable=['media_asset_id','title_ar','body_ar','type','slug','title','body','metadata','status','event_date','expires_at','source_url'];
    protected function casts():array{return ['metadata'=>'array','event_date'=>'date','expires_at'=>'date'];}
    public function scopeVisible($q){return $q->where('status','published');}
}
