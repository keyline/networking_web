<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessPortfolioItem extends Model
{
    protected $guarded = [];
    protected $casts = ['is_active' => 'boolean'];
}
