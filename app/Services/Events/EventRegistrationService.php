<?php
namespace App\Services\Events;

use App\Models\Events\Event;
use App\Models\Events\EventOrder;
use App\Models\Events\EventOrderItem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EventRegistrationService
{
 public function createOrder(Event $event,array $data):EventOrder
 {
  return DB::transaction(function()use($event,$data){
   $event=Event::query()->lockForUpdate()->findOrFail($event->id);
   if($event->status!=='published'||$event->starts_at->isPast()) throw new \DomainException('Registration is not available for this event.');
   if($event->registration_opens_at&&$event->registration_opens_at->isFuture()) throw new \DomainException('Registration has not opened yet.');
   if($event->registration_closes_at&&$event->registration_closes_at->isPast()) throw new \DomainException('Registration is closed.');
   $ticketInputs=collect(Arr::get($data,'tickets',[]))->filter(fn($q)=>(int)$q>0); $seatCount=$ticketInputs->sum(fn($q)=>(int)$q);
   if($seatCount<1||$seatCount>$event->max_seats_per_order) throw new \DomainException('Select between 1 and '.$event->max_seats_per_order.' seats.');
   if(!is_null($event->capacity)&&$seatCount>$event->available_seats) throw new \DomainException('Only '.$event->available_seats.' seats remain.');
   $subtotal=0;$items=[];
   foreach($ticketInputs as $ticketId=>$quantity){$ticket=$event->tickets()->lockForUpdate()->findOrFail($ticketId);$quantity=(int)$quantity;if(!$ticket->is_active||($ticket->sales_start_at&&$ticket->sales_start_at->isFuture())||($ticket->sales_end_at&&$ticket->sales_end_at->isPast()))throw new \DomainException($ticket->name.' is unavailable.');if($quantity<$ticket->minimum_per_order)throw new \DomainException($ticket->name.' requires at least '.$ticket->minimum_per_order.' seat(s).');if($quantity>$ticket->maximum_per_order||(!is_null($ticket->capacity)&&$quantity>$ticket->available_quantity))throw new \DomainException('Not enough '.$ticket->name.' tickets are available.');$line=round((float)$ticket->price*$quantity,2);$subtotal+=$line;$items[]=['item_type'=>'ticket','item_id'=>$ticket->id,'name'=>$ticket->name,'quantity'=>$quantity,'unit_price'=>$ticket->price,'line_total'=>$line];}
   foreach(collect(Arr::get($data,'addons',[]))->filter(fn($q)=>(int)$q>0) as $addonId=>$quantity){$addon=$event->addons()->lockForUpdate()->findOrFail($addonId);if(!$addon->is_active)continue;$quantity=$addon->pricing_type==='per_order'?1:(int)$quantity;if($addon->pricing_type==='per_seat'&&$quantity>$seatCount)throw new \DomainException($addon->name.' cannot exceed the selected seats.');$line=round((float)$addon->price*$quantity,2);$subtotal+=$line;$items[]=['item_type'=>'addon','item_id'=>$addon->id,'name'=>$addon->name,'quantity'=>$quantity,'unit_price'=>$addon->price,'line_total'=>$line,'metadata'=>['category'=>$addon->category,'pricing_type'=>$addon->pricing_type]];}
   $order=EventOrder::create(['order_number'=>'EVT-'.$event->id.'-'.strtoupper(Str::random(8)),'confirmation_token'=>Str::random(48),'event_id'=>$event->id,'user_id'=>auth('member')->id(),'customer_name'=>$data['customer_name'],'customer_email'=>$data['customer_email'],'customer_phone'=>$data['customer_phone']??null,'company_name'=>$data['company_name'],'gst_number'=>$data['gst_number']??null,'seat_count'=>$seatCount,'subtotal'=>$subtotal,'total_amount'=>$subtotal,'currency'=>$event->currency,'status'=>$subtotal>0?'pending':'paid','payment_provider'=>$subtotal>0?'stripe':'free','responses'=>$data['responses']??null,'expires_at'=>$subtotal>0?now()->addMinutes(30):null,'paid_at'=>$subtotal>0?null:now()]);
   foreach($items as $item)$order->items()->create($item);
   $attendees=Arr::get($data,'attendees',[]);for($i=0;$i<$seatCount;$i++){ $person=$attendees[$i]??[];$order->attendees()->create(['ticket_type_id'=>$this->ticketForSeat($items,$i),'first_name'=>$person['first_name']??($i===0?$data['customer_name']:'Guest '.($i+1)),'last_name'=>$person['last_name']??null,'email'=>$person['email']??($i===0?$data['customer_email']:null),'phone'=>$person['phone']??($i===0?($data['customer_phone']??null):null),'company_name'=>$person['company_name']??$data['company_name'],'designation'=>$person['designation']??null,'responses'=>$person['responses']??null]);}
   return $order->fresh(['event','items','attendees']);
  });
 }
 private function ticketForSeat(array $items,int $seatIndex):?int { $offset=0;foreach($items as $item){if($item['item_type']!=='ticket')continue;$offset+=(int)$item['quantity'];if($seatIndex<$offset)return (int)$item['item_id'];}return null; }
}
