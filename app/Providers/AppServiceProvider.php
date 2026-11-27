<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use App\Models\{Product,Variant,ContentEntry,CropPeriod,MediaAsset,PublicDocument,User,SourcingRequest,GeneralEnquiry};
use App\Observers\AuditObserver;
class AppServiceProvider extends ServiceProvider{
 public function boot():void{foreach([Product::class,Variant::class,ContentEntry::class,CropPeriod::class,MediaAsset::class,PublicDocument::class,User::class,SourcingRequest::class,GeneralEnquiry::class] as $model)$model::observe(AuditObserver::class);}
}