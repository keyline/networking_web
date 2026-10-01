<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicRegistrationSetting extends Model
{
    protected $guarded = [];
    protected $casts = ['enabled' => 'boolean'];

    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1], ['enabled' => false]);
    }
}
