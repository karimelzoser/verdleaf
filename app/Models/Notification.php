<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Notification extends Model {
 protected $table='notification_outbox';
 protected $fillable=['kind','reference','attempts','next_attempt_at','sent_at','failed_at'];
 protected function casts():array{return ['next_attempt_at'=>'datetime','sent_at'=>'datetime','failed_at'=>'datetime'];}
}