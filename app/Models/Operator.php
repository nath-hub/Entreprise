<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operator extends Model
{
    use HasFactory;

     public $incrementing = false; // UUID

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'code',
        'country_id',
        'api_endpoint',
        'commission_rate',
        'is_active',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
