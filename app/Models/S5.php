<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class S5 extends Model
{
    use HasFactory;

    protected $table = 's5';

    protected $fillable = [
        'estado',
        'comando_id',
        'flux1',
        'flux2',
    ];

    public function comando()
    {
        return $this->belongsTo(ComandoHardware::class);
    }
}
