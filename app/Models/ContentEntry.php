<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ContentEntry extends Model {
    protected $fillable=['video_asset_id','public_document_id','media_asset_id','title_ar','body_ar','type','slug','title','body','metadata','status','event_date','expires_at','source_url'];
    protected function casts():array{return ['metadata'=>'array','event_date'=>'date','expires_at'=>'date'];}
    public function scopeVisible($q){return $q->where('status','published');}
 public function media(){return $this->belongsTo(MediaAsset::class,'media_asset_id')->where('status','published');}
 public function document(){return $this->belongsTo(PublicDocument::class,'public_document_id')->where('status','published');}
 public function video(){return $this->belongsTo(MediaAsset::class,'video_asset_id')->where('status','published')->whereIn('mime',['video/mp4','video/webm']);}
}
