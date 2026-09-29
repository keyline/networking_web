<?php

namespace App\Services;

use Carbon\Carbon;

class MembershipRenewalCalculator
{
    public function nextDate(Carbon $startDate, int $durationMonths, string $basis, int $anchorMonth = 1, int $anchorDay = 1): Carbon
    {
        if ($basis === 'joining_date') {
            return $startDate->copy()->addMonthsNoOverflow($durationMonths)->startOfDay();
        }

        $anchorMonth = min(12, max(1, $anchorMonth));
        $anchorDay = min(31, max(1, $anchorDay));
        $interval = in_array($durationMonths, [1, 6, 12], true) ? $durationMonths : 12;
        $candidate = $this->safeDate($startDate->year, $anchorMonth, $anchorDay);

        while ($candidate->lte($startDate)) {
            $candidate = $this->safeDate(
                $candidate->copy()->addMonthsNoOverflow($interval)->year,
                $candidate->copy()->addMonthsNoOverflow($interval)->month,
                $anchorDay
            );
        }

        return $candidate->startOfDay();
    }

    private function safeDate(int $year, int $month, int $day): Carbon
    {
        $lastDay = Carbon::create($year, $month, 1)->daysInMonth;

        return Carbon::create($year, $month, min($day, $lastDay));
    }
}
