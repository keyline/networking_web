<?php
namespace App\Services\Events;

use App\Models\Events\EventOrder;
use App\Models\GeneralSetting;
use Illuminate\Support\Facades\Http;

class StripeEventCheckoutService
{
 private function secret():string { $s=GeneralSetting::findOrFail(1);$key=(int)$s->stripe_payment_type===2?$s->stripe_live_sk:$s->stripe_sandbox_sk;if(!$key)throw new \RuntimeException('Stripe is not configured.');return $key; }
 public function create(EventOrder $order):string
 {
  $payload=['mode'=>'payment','success_url'=>route('events.checkout.success',$order).'?session_id={CHECKOUT_SESSION_ID}','cancel_url'=>route('events.show',$order->event->slug).'?payment=cancelled','customer_email'=>$order->customer_email,'client_reference_id'=>$order->order_number,'metadata[order_id]'=>$order->id];
  foreach($order->items as $i=>$item){$payload["line_items[$i][quantity]"]=$item->quantity;$payload["line_items[$i][price_data][currency]"]=strtolower($order->currency);$payload["line_items[$i][price_data][unit_amount]"]=(int)round((float)$item->unit_price*100);$payload["line_items[$i][price_data][product_data][name]"]=$item->name;}
  $response=Http::asForm()->withToken($this->secret())->post('https://api.stripe.com/v1/checkout/sessions',$payload)->throw()->json();
  $order->update(['checkout_session_id'=>$response['id']]); return $response['url'];
 }
 public function verify(EventOrder $order,string $sessionId):bool
 {
  if($order->checkout_session_id!==$sessionId)return false;$session=Http::withToken($this->secret())->get('https://api.stripe.com/v1/checkout/sessions/'.$sessionId)->throw()->json();
  if(($session['payment_status']??null)!=='paid'||(int)($session['amount_total']??-1)!==(int)round((float)$order->total_amount*100))return false;
  $order->update(['status'=>'paid','payment_reference'=>$session['payment_intent']??$sessionId,'paid_at'=>now(),'expires_at'=>null]);return true;
 }
}
