<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EstadoSistema extends Model
{
    use HasFactory;

    protected $table = 'estado_sistemas';

    protected $fillable = [
        's0_id',
        's1_id',
        's2_id',
        's3_id',
        's4_id',
        's5_id',
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

    public function s0()
    {
        return $this->belongsTo(S0::class, 's0_id', 'id');
    }

    public function s1()
    {
        return $this->belongsTo(S1::class, 's1_id', 'id');
    }

    public function s2()
    {
        return $this->belongsTo(S2::class, 's2_id', 'id');
    }

    public function s3()
    {
        return $this->belongsTo(S3::class, 's3_id', 'id');
    }

    public function s4()
    {
        return $this->belongsTo(S4::class, 's4_id', 'id');
    }

    public function s5()
    {
        return $this->belongsTo(S5::class, 's5_id', 'id');
    }
}
