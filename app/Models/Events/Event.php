<?php
namespace App\Models\Events;
use Illuminate\Database\Eloquent\Model;
class Event extends Model {
 protected $guarded=[]; protected $casts=['starts_at'=>'datetime','ends_at'=>'datetime','registration_opens_at'=>'datetime','registration_closes_at'=>'datetime','requires_login'=>'boolean','allowed_user_types'=>'array','allow_guest_registration'=>'boolean','collect_attendee_details'=>'boolean'];
 public function tickets(){return $this->hasMany(EventTicketType::class)->orderBy('sort_order');}
 public function addons(){return $this->hasMany(EventAddon::class)->orderBy('sort_order');}
 public function fields(){return $this->hasMany(EventFormField::class)->orderBy('sort_order');}
 public function orders(){return $this->hasMany(EventOrder::class);}
 public function getConfirmedSeatsAttribute(){return (int)$this->orders()->whereIn('status',['pending','paid'])->where(fn($q)=>$q->where('status','paid')->orWhere('expires_at','>',now()))->sum('seat_count');}
 public function getAvailableSeatsAttribute(){return is_null($this->capacity)?null:max(0,$this->capacity-$this->confirmed_seats);}
}
