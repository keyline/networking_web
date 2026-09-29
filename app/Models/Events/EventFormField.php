<?php
namespace App\Models\Events;
use Illuminate\Database\Eloquent\Model;
class EventFormField extends Model { protected $guarded=[]; protected $casts=['options'=>'array','is_required'=>'boolean']; public function event(){return $this->belongsTo(Event::class);} }
