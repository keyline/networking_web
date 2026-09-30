<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageSection extends Model
{
    protected $guarded = [];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    public function items()
    {
        return $this->hasMany(HomepageSectionItem::class)->orderBy('sort_order')->orderBy('id');
    }
}
