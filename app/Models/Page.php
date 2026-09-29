<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Page extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $fillable = [
        'page_name',
        'page_slug',
        'page_content',
        'page_image',
        'page_banner_image',
        'page_video',
        'parent_id',
        'nav_label',
        'nav_location',
        'nav_order',
        'template',
        'meta_title',
        'meta_description',
        'published_at',
        'status',
        'created_by',
        'updated_by',
        'company_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('nav_order')->orderBy('page_name');
    }
}
