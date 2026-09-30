<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessPortfolio extends Model
{
    protected $guarded = [];

    protected $casts = [
        'whatsapp_enabled' => 'boolean',
        'contact_form_enabled' => 'boolean',
        'is_published' => 'boolean',
        'published_snapshot' => 'array',
        'published_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(BusinessPortfolioItem::class, 'company_id', 'company_id')->orderBy('sort_order');
    }

    public function media()
    {
        return $this->hasMany(BusinessPortfolioMedia::class, 'company_id', 'company_id')->orderBy('sort_order');
    }
}
