<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Models\{User,Product,Variant,ContentEntry,CropPeriod,PublicDocument,UrlRedirect,GeneralEnquiry};
class AdminOperationsTest extends TestCase{
 use RefreshDatabase;
 private function admin():User{return User::create(['name'=>'Synthetic admin','email'=>'admin-ops@example.test','password'=>'test-only-long-password','role'=>'admin']);}
 public function test_product_variant_crop_and_approval_workflow():void{
  $this->actingAs($this->admin())->post('/admin/products',['name'=>'QA botanical','category'=>'Herbs'])->assertRedirect();
  $p=Product::where('slug','qa-botanical')->firstOrFail();
  $this->assertSame('draft',$p->status);
  $payload=['name'=>$p->name,'description'=>'Synthetic review content','source_url'=>'https://example.test/reference','status'=>'published','name_ar'=>'اختبار','description_ar'=>'محتوى اختباري'];
  $this->post('/admin/products/'.$p->id,$payload)->assertSessionHasErrors('approved');
  $this->post('/admin/products/'.$p->id,$payload+['approved'=>1])->assertRedirect();$this->assertSame('published',$p->fresh()->status);
  $this->post('/admin/products/'.$p->id.'/variants',['sku'=>'QA-1','form'=>'Whole'])->assertRedirect();$v=Variant::where('sku','QA-1')->firstOrFail();
  $this->post('/admin/variants/'.$v->id,['form'=>'Whole','status'=>'published','approved'=>1,'specifications'=>"Grade A\nTest only",'packaging'=>'Synthetic packing'])->assertRedirect();
  $this->assertSame('published',$v->fresh()->status);
  $crop=['product_id'=>$p->id,'region'=>'Test region','crop_year'=>2026,'month'=>4,'stage'=>'harvest','source_url'=>'https://example.test/calendar','status'=>'published'];
  $this->post('/admin/crop-periods',$crop)->assertSessionHasErrors('approved');$this->post('/admin/crop-periods',$crop+['approved'=>1])->assertRedirect();
  $this->assertDatabaseHas('crop_periods',['product_id'=>$p->id,'status'=>'published']);$this->get('/crop-calendar?year=2026')->assertOk()->assertSee('Test region');
 }
 public function test_certification_evidence_and_expiry():void{
  $this->actingAs($this->admin());$d=['type'=>'certificate','title'=>'Synthetic certificate','body'=>'Test only','status'=>'published','approved'=>1];
  $this->post('/admin/content',$d)->assertSessionHasErrors(['issuer','expires_at']);
  $this->post('/admin/content',$d+['issuer'=>'Test issuer','certificate_number'=>'TEST-001','scope'=>'Test scope','expires_at'=>today()->addMonth()->toDateString(),'source_url'=>'https://example.test/certificate'])->assertRedirect()->assertSessionHasNoErrors();
  $this->assertDatabaseHas('content_entries',['title'=>'Synthetic certificate','status'=>'published']);
 }
 public function test_documents_and_redirects():void{
  Storage::fake('local');$this->actingAs($this->admin());
  $this->post('/admin/documents',['pdf'=>UploadedFile::fake()->createWithContent('not.pdf','not PDF'),'title'=>'Invalid','kind'=>'catalogue','approved'=>1])->assertSessionHasErrors('pdf');
  $this->post('/admin/documents',['pdf'=>UploadedFile::fake()->createWithContent('test.pdf',"%PDF-1.4\n% Synthetic document\n%%EOF"),'title'=>'Test catalogue','kind'=>'catalogue','approved'=>1])->assertRedirect()->assertSessionHasNoErrors();
  $doc=PublicDocument::where('title','Test catalogue')->firstOrFail();Storage::disk('local')->assertExists($doc->path);
  $this->get('/documents/'.$doc->id)->assertOk();$this->post('/admin/documents/'.$doc->id.'/archive')->assertRedirect();$this->get('/documents/'.$doc->id)->assertNotFound();
  $this->post('/admin/redirects',['old_path'=>'/test-old','new_path'=>'/products'])->assertRedirect();$this->get('/test-old')->assertStatus(301)->assertRedirect('/products');
  $this->post('/admin/redirects',['old_path'=>'/test-invalid','new_path'=>'/admin'])->assertStatus(422);
  $id=UrlRedirect::where('old_path','/test-old')->value('id');$this->post('/admin/redirects/'.$id.'/remove')->assertRedirect();$this->get('/test-old')->assertNotFound();
 }
 public function test_general_enquiry_status_and_role_denial():void{
  $admin=$this->admin();$this->actingAs($admin);
  $e=GeneralEnquiry::create(['reference'=>'QA-TEST','type'=>'visit','company'=>'Synthetic company','name'=>'Synthetic contact','email'=>'example@example.test','country'=>'Egypt','message'=>'Test enquiry','status'=>'new','consent_at'=>now()]);
  $this->post('/admin/enquiries/'.$e->id,['status'=>'reviewed'])->assertRedirect();$this->assertSame('reviewed',$e->fresh()->status);
  $editor=User::create(['name'=>'Editor','email'=>'editor-ops@example.test','password'=>'test-only-long-password','role'=>'editor']);$this->actingAs($editor);
  foreach(['/admin/operations','/admin/documents','/admin/growth','/admin/media','/admin/seo','/admin/security','/admin/catalogue/import'] as $path)$this->get($path)->assertForbidden();
  $this->post('/admin/enquiries/'.$e->id,['status'=>'closed'])->assertForbidden();
 }
}
