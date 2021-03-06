<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Commodity extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'snippets',
        'spot',
        'change',
        'prices',
        'supply_demand',
        'applications',
        'sources',
        'status',
    ];

    public $casts = [
        'snippets' => 'json',
        'prices' => 'json',
        'change' => 'json',
        'supply_demand' => 'json',
        'applications' => 'json',
        'sources' => 'json',
    ];

    public function articles()
    {
        return $this->morphMany('App\Article', 'item');
    }

    public function registry()
    {
        return $this->morphOne('App\Registry', 'entry');
    }

    public static $topics = [
        'prices',
        'supply',
        'demand'
    ];
}
