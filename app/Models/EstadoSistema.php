<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function s0()
    {
        return $this->belongsTo(S0::class);
    }

    public function s1()
    {
        return $this->belongsTo(S1::class);
    }

    public function s2()
    {
        return $this->belongsTo(S2::class);
    }

    public function s3()
    {
        return $this->belongsTo(S3::class);
    }

    public function s4()
    {
        return $this->belongsTo(S4::class);
    }

    public function s5()
    {
        return $this->belongsTo(S5::class);
    }
}
