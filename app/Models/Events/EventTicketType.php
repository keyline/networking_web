<?php
namespace App\Models\Events;
use Illuminate\Database\Eloquent\Model;
class EventTicketType extends Model { protected $guarded=[]; protected $casts=['price'=>'decimal:2','sales_start_at'=>'datetime','sales_end_at'=>'datetime','is_active'=>'boolean']; public function event(){return $this->belongsTo(Event::class);} public function getSoldQuantityAttribute(){return (int)EventOrderItem::where('item_type','ticket')->where('item_id',$this->id)->whereHas('order',fn($q)=>$q->where('status','paid')->orWhere(fn($p)=>$p->where('status','pending')->where('expires_at','>',now())))->sum('quantity');} public function getAvailableQuantityAttribute(){return is_null($this->capacity)?null:max(0,$this->capacity-$this->sold_quantity);} }
