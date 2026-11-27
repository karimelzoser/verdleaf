<?php
namespace App\Http\Controllers;
use App\Models\MediaAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class MediaLibrary {
 public function index(Request $r){abort_unless($r->user()?->role==='admin',403);return view('admin.media',['assets'=>MediaAsset::latest()->paginate(30)]);}
 public function store(Request $r){
  abort_unless($r->user()?->role==='admin',403);
  $d=$r->validate(['image'=>'required|image|mimes:jpeg,jpg,png,webp|max:5120|dimensions:max_width=6000,max_height=6000','alt'=>'required|string|max:255','credit'=>'required|string|max:255','approved'=>'accepted']);
  $file=$r->file('image');$mime=$file->getMimeType();abort_unless(in_array($mime,['image/jpeg','image/png','image/webp'],true),422);
  $path=$file->store('approved-media','local');abort_unless($path,500);
  MediaAsset::create(['path'=>$path,'mime'=>$mime,'alt'=>$d['alt'],'credit'=>$d['credit'],'status'=>'published']);return back()->with('message','Approved media uploaded.');
 }
 public function show(MediaAsset $asset){abort_unless($asset->status==='published',404);abort_unless(Storage::disk('local')->exists($asset->path),404);return Storage::disk('local')->response($asset->path,null,['Content-Type'=>$asset->mime,'Cache-Control'=>'public, max-age=3600','X-Content-Type-Options'=>'nosniff'],'inline');}
}