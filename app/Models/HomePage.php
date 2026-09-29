<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class HomePage extends Model{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sec3_title',
        'sec3_short_description',
        'sec3_long_description',
        'sec3_link',
        'sec3_image',
        'sec4_title1',
        'sec4_title2',
        'sec4_description',
        'sec4_link1_name',
        'sec4_link1_url',
        'sec4_link2_name',
        'sec4_link2_url',
        'sec6_title',
        'sec6_call_us',
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
