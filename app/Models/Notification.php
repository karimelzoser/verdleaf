<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Notification extends Model {
 protected $table='notification_outbox';
 protected $fillable=['staff_sent_at','buyer_sent_at','kind','reference','attempts','next_attempt_at','sent_at','failed_at'];
 protected function casts():array{return ['staff_sent_at'=>'datetime','buyer_sent_at'=>'datetime','next_attempt_at'=>'datetime','sent_at'=>'datetime','failed_at'=>'datetime'];}
}