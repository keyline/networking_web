<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageSectionItem extends Model
{
    protected $guarded = [];

    protected $casts = [
        'meta' => 'array',
        'is_active' => 'boolean',
    ];

    public function section()
    {
        return $this->belongsTo(HomepageSection::class, 'homepage_section_id');
    }
}
