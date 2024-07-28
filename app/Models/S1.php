<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class S1 extends Model
{
    use HasFactory;

    protected $table = 's1';

    protected $fillable = [
        'estado',
        'comando_id',
        'sensor1',
        'sensor2',
        'valvula14',
    ];

    public function comando()
    {
        return $this->belongsTo(ComandoHardware::class);
    }
}

