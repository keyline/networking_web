<?php

namespace Tests\Feature;

use App\Models\Events\Event;
use App\Models\Events\EventOrder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OpenEventRegistrationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_open_event_uses_one_attendee_without_quantity_or_extra_participant_fields(): void
    {
        $event = Event::create([
            'title' => 'Open Community Event',
            'slug' => 'open-community-event-'.uniqid(),
            'starts_at' => now()->addWeek(),
            'ends_at' => now()->addWeek()->addHours(2),
            'status' => 'published',
            'currency' => 'INR',
            'cover_image' => 'uploads/events/open-community-banner.jpg',
            'requires_login' => false,
            'collect_attendee_details' => true,
        ]);
        $ticket = $event->tickets()->create([
            'name' => 'General admission',
            'price' => 0,
            'maximum_per_order' => 10,
            'is_active' => true,
        ]);

        $this->get(route('events.index'))
            ->assertOk()
            ->assertSee(url('public/'.$event->cover_image), false)
            ->assertSee($event->title.' event banner');

        $this->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('data-portal-home-logo', false)
            ->assertSee('class="portal-brand" href="'.url('/').'"', false)
            ->assertSee('class="all-events" href="'.route('events.index').'">All Events</a>', false)
            ->assertSee('Choose admission')
            ->assertSee('name="ticket_id"', false)
            ->assertSee('1 attendee')
            ->assertSee('<button class="pay">Register</button>', false)
            ->assertSee('id="payment-note"', false)
            ->assertSee('id="payment-note" style="font-size:12px;text-align:center" hidden', false)
            ->assertDontSee('Attendee details')
            ->assertDontSee('name="tickets[', false)
            ->assertDontSee('left');

        $response = $this->post(route('events.register', $event), [
            'ticket_id' => $ticket->id,
            'tickets' => [$ticket->id => 7],
            'customer_name' => 'Open Visitor',
            'customer_email' => 'open-visitor@example.test',
            'company_name' => 'Visitor Company',
            'attendees' => [
                ['first_name' => 'Extra Participant', 'company_name' => 'Other Company', 'designation' => 'Guest'],
            ],
        ]);

        $order = EventOrder::where('event_id', $event->id)->latest('id')->firstOrFail();
        $response->assertRedirect(route('events.confirmation', [$order, $order->confirmation_token]));
        $this->assertSame(1, $order->seat_count);
        $this->assertCount(1, $order->attendees);
        $this->assertSame('Open Visitor', $order->attendees->first()->first_name);
    }
}
