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
}
