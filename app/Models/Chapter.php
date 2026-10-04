<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    protected $guarded = [];

    protected $casts = [
        'established_on' => 'date',
    ];

    public function members()
    {
        return $this->hasMany(ChapterMember::class);
    }

    public function activeMembers()
    {
        return $this->members()->where('status', 'active');
    }

    public function getRegularMeetingLabelAttribute(): string
    {
        $schedule = match ($this->meeting_frequency) {
            'weekly' => 'Weekly on '.$this->meeting_day,
            'monthly' => 'Monthly on day '.$this->meeting_day_of_month,
            'twice_monthly' => 'Twice monthly on days '.$this->meeting_day_of_month.' and '.$this->meeting_second_day_of_month,
            default => 'Not scheduled',
        };

        return $this->meeting_time && $this->meeting_frequency !== 'not_scheduled'
            ? $schedule.' at '.date('h:i A', strtotime($this->meeting_time))
            : $schedule;
    }
}
