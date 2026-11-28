<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Services\StaffSecurity;
use Illuminate\Support\Facades\{Hash,DB};
class StaffSecurityTest extends TestCase{
 use RefreshDatabase;
 private function account(string $role='admin'):User{
  $u=User::create(['name'=>'Test staff','email'=>$role.'@example.test','password'=>'test-only-long-password','role'=>$role]);
  $u->forceFill(['totp_secret'=>StaffSecurity::generateSecret(),'recovery_hashes'=>[Hash::make('test-recovery-code')]])->save();return $u;
 }
 private function password(User $u):void{$this->post('/login',['email'=>$u->email,'password'=>'test-only-long-password'])->assertRedirect('/login/verify');}
 public function test_rfc_totp_known_vector():void{
  $this->assertSame('287082',StaffSecurity::code('GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ',1));
 }
 public function test_password_alone_cannot_open_admin():void{
  config(['verdleaf.staff_mfa'=>true]);$u=$this->account();$this->password($u);$this->assertGuest();
  $this->get('/admin')->assertRedirect('/login');$this->get('/admin/security')->assertRedirect('/login');
  $this->post('/login/verify',['code'=>'invalid'])->assertSessionHasErrors('code');$this->assertGuest();
 }
 public function test_mfa_login_replay_and_revocation():void{
  config(['verdleaf.staff_mfa'=>true]);$u=$this->account();$code=StaffSecurity::code($u->totp_secret,intdiv(time(),30));$this->password($u);
  $this->post('/login/verify',['code'=>$code])->assertRedirect('/admin');$this->assertAuthenticatedAs($u);
  $this->get('/admin')->assertOk()->assertSee('Operations overview')->assertHeader('X-Frame-Options','DENY');
  $this->get('/admin/security')->assertOk()->assertDontSee($u->totp_secret);
  $this->assertNotNull($u->fresh()->last_totp_step);$this->assertFalse(StaffSecurity::consume($u->id,$code));
  $u->fresh()->forceFill(['session_version'=>1])->save();$this->get('/admin')->assertRedirect('/login');$this->assertGuest();
 }
 public function test_recovery_is_single_use_and_staff_is_scoped():void{
  config(['verdleaf.staff_mfa'=>true]);$u=$this->account('editor');$this->password($u);
  $this->post('/login/verify',['code'=>'test-recovery-code'])->assertRedirect('/staff');$this->get('/staff')->assertOk();$this->get('/admin/security')->assertForbidden();
  $this->post('/admin/products',[])->assertForbidden();$this->assertFalse(StaffSecurity::consume($u->id,'test-recovery-code'));
  $this->withSession(['staff_last'=>time()-901])->get('/staff')->assertRedirect('/login');$this->assertGuest();
 }
 public function test_mfa_enrollment_required_and_disabled_account_rejected():void{
  config(['verdleaf.staff_mfa'=>true]);$u=$this->account();$u->forceFill(['totp_secret'=>null])->save();
  $this->post('/login',['email'=>$u->email,'password'=>'test-only-long-password'])->assertRedirect('/login');$this->assertGuest();
  $u->forceFill(['disabled_at'=>now()])->save();$this->post('/login',['email'=>$u->email,'password'=>'test-only-long-password'])->assertSessionHasErrors('email');$this->assertGuest();
 }
 public function test_secrets_are_encrypted_and_hidden():void{
  $u=$this->account();$secret=$u->totp_secret;$raw=DB::table('users')->where('id',$u->id)->first();$this->assertNotSame($secret,$raw->totp_secret);$this->assertStringNotContainsString($secret,$u->toJson());$this->assertStringNotContainsString('test-recovery-code',$raw->recovery_hashes);
 }
}
