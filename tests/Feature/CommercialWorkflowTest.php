<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{User,SourcingRequest,CommercialCase};
class CommercialWorkflowTest extends TestCase{
 use RefreshDatabase;
 public function test_commercial_flow_requires_authorization_and_valid_transitions():void{
  $admin=User::create(['name'=>'QA admin','email'=>'commercial-admin@example.test','password'=>'test-only-password','role'=>'admin']);
  $this->get('/admin/commercial')->assertRedirect('/login');
  $this->actingAs($admin);
  $request=SourcingRequest::create(['reference'=>'QA-COMMERCIAL','idempotency_key'=>hash('sha256','qa-commercial'),'company'=>'Synthetic buyer','email'=>'buyer@example.test','contact_name'=>'QA','country'=>'Egypt','status'=>'new','consent_at'=>now()]);
  $p=\App\Models\Product::create(['name'=>'QA Chamomile','slug'=>'qa-chamomile','category'=>'Flowers','status'=>'draft']);
  $v=\App\Models\Variant::create(['product_id'=>$p->id,'sku'=>'QA-COMMERCIAL-1','form'=>'Whole','status'=>'draft']);
  $request->items()->create(['variant_id'=>$v->id,'product_name'=>'Chamomile','form'=>'Whole','grade'=>'Reference','quantity_kg'=>10.125,'purposes'=>['quote']]);
  $this->post('/admin/commercial',['kind'=>'quote','title'=>'Test quotation','sourcing_request_id'=>$request->id])->assertRedirect();
  $q=CommercialCase::where('kind','quote')->firstOrFail();
  $this->post('/admin/commercial/'.$q->id.'/status',['status'=>'sent'])->assertStatus(409);
  $this->post('/admin/commercial/'.$q->id.'/price',['currency'=>'USD','terms'=>'FOB','valid_until'=>today()->addDay()->toDateString(),'prices'=>[250]])->assertRedirect()->assertSessionHasNoErrors();
  $this->assertSame(2531,$q->fresh()->total_minor);
  $this->post('/admin/commercial/'.$q->id.'/status',['status'=>'approved'])->assertSessionHasErrors('confirmed');
  $this->post('/admin/commercial/'.$q->id.'/status',['status'=>'approved','confirmed'=>1])->assertRedirect();
  $this->post('/admin/commercial/'.$q->id.'/price',['currency'=>'USD','terms'=>'FOB','valid_until'=>today()->addDay()->toDateString(),'prices'=>[300]])->assertStatus(409);
  $this->get('/admin/commercial/'.$q->id.'/print')->assertOk()->assertSee('25.31');
  $this->post('/admin/commercial/'.$q->id.'/status',['status'=>'sent','confirmed'=>1])->assertRedirect();
  $this->post('/admin/commercial',['kind'=>'sample','title'=>'Sample request','sourcing_request_id'=>$request->id])->assertRedirect();
  $sample=CommercialCase::where('kind','sample')->firstOrFail();$this->post('/admin/commercial/'.$sample->id.'/status',['status'=>'dispatched'])->assertStatus(409);
  $this->post('/admin/commercial/'.$sample->id.'/status',['status'=>'approved'])->assertRedirect();
  $this->post('/admin/commercial',['kind'=>'task','title'=>'Follow up'])->assertRedirect();
  $this->get('/admin/commercial?kind=task')->assertOk()->assertSee('Follow up');
  $editor=User::create(['name'=>'QA editor','email'=>'commercial-editor@example.test','password'=>'test-only-password','role'=>'editor']);
  $this->actingAs($editor)->get('/admin/commercial/'.$q->id.'/print')->assertForbidden();
  $this->post('/admin/commercial/'.$q->id.'/status',['status'=>'accepted'])->assertForbidden();
 }
}