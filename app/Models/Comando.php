<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comando extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'descripcion'];

    public function cultivos()
    {
        return $this->belongsToMany(Cultivo::class, 'cultivo_comando');
    }

    public function cultivosActuales()
    {
        return $this->hasMany(Cultivo::class, 'comando_actual_id');
    }
}
