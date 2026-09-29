<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Notification extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'to_users',
        'users',
        'is_send',
    ];
}
