<?php

namespace Tests\Unit\Events;

use App\Models\Events\Event;
use App\Services\Events\EventRegistrationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EventRegistrationServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        DB::reconnect('sqlite');
        (require database_path('migrations/2026_09_29_120000_create_event_management_tables.php'))->up();
        (require database_path('migrations/2026_09_29_130000_add_company_fields_to_event_registrations.php'))->up();
    }

    public function test_it_calculates_tickets_and_food_on_the_server(): void
    {
        $event = Event::create(['title'=>'Summit','slug'=>'summit','starts_at'=>now()->addMonth(),'ends_at'=>now()->addMonth()->addDay(),'status'=>'published','max_seats_per_order'=>5,'currency'=>'INR']);
        $ticket = $event->tickets()->create(['name'=>'Delegate','price'=>500,'maximum_per_order'=>5,'is_active'=>true]);
        $meal = $event->addons()->create(['name'=>'Lunch','category'=>'food','price'=>200,'pricing_type'=>'per_seat','is_active'=>true]);
        $order = app(EventRegistrationService::class)->createOrder($event, ['customer_name'=>'Ada','customer_email'=>'ada@example.test','company_name'=>'Analytical Engines Ltd','tickets'=>[$ticket->id=>2],'addons'=>[$meal->id=>2]]);

        $this->assertSame(2, $order->seat_count);
        $this->assertSame('1400.00', $order->total_amount);
        $this->assertSame('pending', $order->status);
        $this->assertCount(2, $order->attendees);
        $this->assertNotEmpty($order->confirmation_token);
    }

    public function test_it_rejects_orders_over_the_event_capacity(): void
    {
        $event = Event::create(['title'=>'Small','slug'=>'small','starts_at'=>now()->addMonth(),'ends_at'=>now()->addMonth()->addDay(),'status'=>'published','capacity'=>1,'max_seats_per_order'=>5,'currency'=>'INR']);
        $ticket = $event->tickets()->create(['name'=>'Seat','price'=>0,'maximum_per_order'=>5,'is_active'=>true]);
        $this->expectException(\DomainException::class);
        app(EventRegistrationService::class)->createOrder($event, ['customer_name'=>'Ada','customer_email'=>'ada@example.test','company_name'=>'Analytical Engines Ltd','tickets'=>[$ticket->id=>2]]);
    }
}
