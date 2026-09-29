<?php
namespace App\Models\Events;
use Illuminate\Database\Eloquent\Model;
class EventOrder extends Model { protected $guarded=[]; protected $casts=['subtotal'=>'decimal:2','discount_amount'=>'decimal:2','tax_amount'=>'decimal:2','total_amount'=>'decimal:2','responses'=>'array','expires_at'=>'datetime','paid_at'=>'datetime']; public function event(){return $this->belongsTo(Event::class);} public function items(){return $this->hasMany(EventOrderItem::class);} public function attendees(){return $this->hasMany(EventAttendee::class);} }
