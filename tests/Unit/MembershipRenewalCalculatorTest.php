<?php

namespace Tests\Unit;

use App\Services\MembershipRenewalCalculator;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class MembershipRenewalCalculatorTest extends TestCase
{
    private MembershipRenewalCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new MembershipRenewalCalculator();
    }

    public function test_yearly_organization_cycle_renews_on_first_of_march(): void
    {
        $this->assertSame('2027-03-01', $this->calculator->nextDate(Carbon::parse('2026-04-15'), 12, 'calendar', 3, 1)->toDateString());
        $this->assertSame('2026-03-01', $this->calculator->nextDate(Carbon::parse('2026-02-15'), 12, 'calendar', 3, 1)->toDateString());
    }

    public function test_half_yearly_cycle_uses_march_and_september(): void
    {
        $this->assertSame('2026-09-01', $this->calculator->nextDate(Carbon::parse('2026-03-20'), 6, 'calendar', 3, 1)->toDateString());
        $this->assertSame('2027-03-01', $this->calculator->nextDate(Carbon::parse('2026-10-01'), 6, 'calendar', 3, 1)->toDateString());
    }

    public function test_monthly_cycle_uses_selected_day_every_month(): void
    {
        $this->assertSame('2026-05-01', $this->calculator->nextDate(Carbon::parse('2026-04-15'), 1, 'calendar', 3, 1)->toDateString());
    }

    public function test_joining_date_basis_uses_member_anniversary(): void
    {
        $this->assertSame('2027-06-18', $this->calculator->nextDate(Carbon::parse('2026-06-18'), 12, 'joining_date', 3, 1)->toDateString());
    }

    public function test_short_month_uses_its_last_day(): void
    {
        $this->assertSame('2027-02-28', $this->calculator->nextDate(Carbon::parse('2027-01-31'), 1, 'calendar', 1, 31)->toDateString());
    }
}
