<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class S4 extends Model
{
    use HasFactory;

    protected $table = 's4';

    protected $fillable = [
        'estado',
        'comando_id',
        'pump3',
        'pump4',
    ];

    public function comando()
    {
        return $this->belongsTo(ComandoHardware::class);
    }
}

