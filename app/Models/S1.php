<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class S1 extends Model
{
    use HasFactory;

    protected $table = 's1';

    protected $fillable = [
        'id',
        'estado',
        'comando_id',
        'sensor1',
        'sensor2',
        'valvula14',
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

