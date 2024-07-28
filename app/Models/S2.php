<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class S2 extends Model
{
    use HasFactory;

    protected $table = 's2';

    protected $fillable = [
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

    public function comando()
    {
        return $this->belongsTo(ComandoHardware::class);
    }
}
