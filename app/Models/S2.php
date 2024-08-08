<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class S2 extends Model
{
    use HasFactory;

    protected $table = 's2';

    protected $fillable = [
        'id',
        'estado',
        'comando_id',
        'valvula1',
        'valvula2',
        'valvula3',
        'valvula4',
        'valvula5',
        'valvula6',
        'valvula7',
        'valvula8',
        'valvula9',
        'valvula10',
        'valvula11',
        'valvula12',
        'valvula13',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function comando()
    {
        return $this->belongsTo(ComandoHardware::class);
    }
}
