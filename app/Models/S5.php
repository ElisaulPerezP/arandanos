<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class S5 extends Model
{
    use HasFactory;

    protected $table = 's5';

    protected $fillable = [
        'id',
        'estado',
        'comando_id',
        'flux1',
        'flux2',
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
