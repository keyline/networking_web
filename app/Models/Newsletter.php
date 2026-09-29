<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Newsletter extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'attachment',
        'to_users',
        'users',
        'is_send',
    ];
}
