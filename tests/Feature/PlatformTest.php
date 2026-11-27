<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{Product,Variant,User,ContentEntry,SourcingRequest};
class PlatformTest extends TestCase {
 use RefreshDatabase;
 private function sample(string $status='published'):array{
  $p=Product::create(['slug'=>'chamomile','name'=>'Chamomile','category'=>'Flowers','status'=>$status,'description'=>'Approved test copy','applications'=>['Tea & infusions']]);
  $v=$p->variants()->create(['sku'=>'TEST-1','form'=>'Whole','status'=>$status]);return [$p,$v];
 }
 public function test_drafts_are_hidden_and_preview_is_noindex():void{
  [$p,$v]=$this->sample('draft');config(['verdleaf.preview'=>false]);
  $this->get('/products/chamomile')->assertNotFound();$this->get('/products')->assertDontSee('Chamomile');
  config(['verdleaf.preview'=>true]);$this->get('/products/chamomile')->assertOk()->assertHeader('X-Robots-Tag','noindex, nofollow');$this->get('/sitemap.xml')->assertNotFound();
 }
 public function test_legacy_draft_inventory():void{
  $this->seed();$this->assertDatabaseCount('products',29);$this->assertSame(29,Product::where('status','draft')->count());$this->assertDatabaseCount('variants',11);
 }
 public function test_public_routes_render():void{
  $this->sample();config(['verdleaf.preview'=>false]);
  foreach(['/','/products','/atlas','/ingredient-finder','/products/chamomile','/inside-verdleaf','/crop-calendar','/certifications','/exhibitions','/visits','/buyer-desk','/sitemap.xml','/robots.txt','/login'] as $url)$this->get($url)->assertOk();
  $this->get('/products?q=chamomile')->assertSee('Chamomile');$this->get('/ingredient-finder?application=Tea%20%26%20infusions')->assertSee('Chamomile');$this->get('/products?q=nonexistent')->assertDontSee('Chamomile');
 }
 public function test_buyer_request_and_privacy_gate():void{
  [$p,$v]=$this->sample();config(['verdleaf.preview'=>true]);
  $this->post('/buyer-desk/items',['variant_id'=>$v->id])->assertRedirect('/buyer-desk');$this->get('/buyer-desk')->assertOk();
  $this->post('/buyer-desk/submit',[])->assertForbidden();config(['verdleaf.preview'=>false]);$this->post('/buyer-desk/submit',[])->assertForbidden();
  ContentEntry::create(['type'=>'privacy','slug'=>'test','title'=>'Test privacy','body'=>'Test only','status'=>'published']);
  $this->post('/buyer-desk/update',['items'=>[$v->id=>['quantity'=>1250,'purposes'=>['quote','sample','documents']]]])->assertRedirect('/buyer-desk');
  $token=session('request_token');
  $data=['company'=>'Test company','email'=>'buyer@example.test','contact_name'=>'Test buyer','country'=>'Germany','consent'=>1,'request_token'=>$token];
  $this->post('/buyer-desk/submit',$data)->assertRedirect('/buyer-desk/receipt');$this->assertDatabaseCount('sourcing_requests',1);$this->assertDatabaseHas('sourcing_items',['variant_id'=>$v->id,'quantity_kg'=>1250]);
  $this->get('/buyer-desk/receipt')->assertSee('Request received');$this->post('/buyer-desk/submit',$data)->assertStatus(419);$this->assertDatabaseCount('sourcing_requests',1);
 }
 public function test_admin_requires_role():void{
  $this->get('/admin')->assertRedirect('/login');
  $editor=User::create(['name'=>'Editor','email'=>'editor@example.test','password'=>'long-test-password','role'=>'editor']);
  $this->actingAs($editor)->get('/admin')->assertForbidden();
  $admin=User::create(['name'=>'Admin','email'=>'admin@example.test','password'=>'long-test-password','role'=>'admin']);
  $this->actingAs($admin)->get('/admin')->assertOk();
 }
}
