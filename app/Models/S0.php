<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class S0 extends Model
{
    use HasFactory;

    protected $table = 's0';

    protected $fillable = [
        'estado',
        'comando_id',
        'sensor3',
    ];

    public function comando()
    {
        return $this->belongsTo(ComandoHardware::class);
    }
}

