<?php
namespace App\Models\Events;
use Illuminate\Database\Eloquent\Model;
class EventAttendee extends Model { protected $guarded=[]; protected $casts=['responses'=>'array','checked_in_at'=>'datetime']; public function order(){return $this->belongsTo(EventOrder::class,'event_order_id');} public function ticket(){return $this->belongsTo(EventTicketType::class,'ticket_type_id');} }
