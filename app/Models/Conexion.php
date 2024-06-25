<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conexion extends Model
{
    use HasFactory;
    protected $fillable = [
        'fecha_unix',
        'cultivo_id',
    ];
    public function cultivo()
    {
        return $this->belongsTo(Cultivo::class);
    }
}
