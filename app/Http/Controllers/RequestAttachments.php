<?php
namespace App\Http\Controllers;
use App\Models\SourcingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class RequestAttachments{
 public function download(Request $r,SourcingRequest $sourcingRequest){
  abort_unless(in_array($r->user()?->role,['admin','sales'],true),403);
  abort_unless($sourcingRequest->attachment_path&&Storage::disk('local')->exists($sourcingRequest->attachment_path),404);
  return Storage::disk('local')->download($sourcingRequest->attachment_path,'specification-'.$sourcingRequest->reference.'.pdf',['Content-Type'=>'application/pdf','X-Content-Type-Options'=>'nosniff','Cache-Control'=>'private, no-store']);
 }
}