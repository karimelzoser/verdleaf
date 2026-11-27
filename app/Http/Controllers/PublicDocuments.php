<?php
namespace App\Http\Controllers;
use App\Models\PublicDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class PublicDocuments{
 private function admin(Request $r){abort_unless($r->user()?->role==='admin',403);}
 public function index(Request $r){$this->admin($r);return view('admin.documents',['docs'=>PublicDocument::latest()->paginate(30)]);}
 public function store(Request $r){
  $this->admin($r);
  $d=$r->validate(['pdf'=>'required|file|mimes:pdf|max:10240','title'=>'required|string|max:160','kind'=>'required|in:certificate,tds,catalogue,company-profile,public-sample-coa','revision'=>'nullable|string|max:100','expires_at'=>'nullable|date','approved'=>'accepted']);
  $file=$r->file('pdf');$handle=fopen($file->getRealPath(),'rb');$magic=fread($handle,5);fclose($handle);abort_unless($magic==='%PDF-',422);
  $path=$file->store('public-documents','local');abort_unless($path,500);
  PublicDocument::create(array_intersect_key($d,array_flip(['title','kind','revision','expires_at']))+['path'=>$path,'status'=>'published']);return back()->with('message','Public PDF uploaded.');
 }
 public function download(PublicDocument $document){
  abort_unless($document->status==='published'&&Storage::disk('local')->exists($document->path),404);
  return Storage::disk('local')->download($document->path,(Str::slug($document->title)?:'document').'.pdf',['Content-Type'=>'application/pdf','X-Content-Type-Options'=>'nosniff','Cache-Control'=>'no-store']);
 }
 public function archive(Request $r,PublicDocument $document){$this->admin($r);$document->update(['status'=>'draft']);return back();}
}