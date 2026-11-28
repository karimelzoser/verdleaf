<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use App\Models\{Product,Variant,ContentEntry,CropPeriod,MediaAsset,PublicDocument,User,SourcingRequest,GeneralEnquiry};
use App\Observers\AuditObserver;
class AppServiceProvider extends ServiceProvider{
 public function boot():void{if($this->app->environment('production')){config(['session.secure'=>true,'session.http_only'=>true,'session.same_site'=>'lax','session.lifetime'=>15]);}foreach([Product::class,Variant::class,ContentEntry::class,CropPeriod::class,MediaAsset::class,PublicDocument::class,User::class,SourcingRequest::class,GeneralEnquiry::class] as $model)$model::observe(AuditObserver::class);}
}