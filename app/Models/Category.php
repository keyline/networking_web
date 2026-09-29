<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class Category extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $fillable = [
        'parent_id',
        'category_name',
        'slug',
        'cover_image',
        'banner_image',
        'short_description',
        'description',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];
}
