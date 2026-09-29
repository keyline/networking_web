<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Quote extends Model{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'quotes'
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    // protected $hidden = [
    // ];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    // protected $casts = [
        
    // ];
}