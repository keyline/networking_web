<?php
namespace App\Models\Events;
use Illuminate\Database\Eloquent\Model;
class EventOrderItem extends Model { protected $guarded=[]; protected $casts=['unit_price'=>'decimal:2','line_total'=>'decimal:2','metadata'=>'array']; public function order(){return $this->belongsTo(EventOrder::class,'event_order_id');} }
