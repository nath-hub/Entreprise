<?php

namespace App\Models;

use App\Traits\MultiDatabaseTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Entreprise extends Model
{
    use HasFactory, MultiDatabaseTrait;

    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'id'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }


    public function setConnection($connection)
    {
        $this->connection = $connection;
        return $this;
    }


    public function fichiers()
    {
        return $this->hasOne(FichierEntreprise::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
