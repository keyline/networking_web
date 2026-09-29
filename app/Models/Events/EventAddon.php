<?php
namespace App\Models\Events;
use Illuminate\Database\Eloquent\Model;
class EventAddon extends Model { protected $guarded=[]; protected $casts=['price'=>'decimal:2','is_active'=>'boolean']; public function event(){return $this->belongsTo(Event::class);} }
