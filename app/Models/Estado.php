<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    use HasFactory;
    protected $fillable = ['nombre', 'descripcion'];
    public function cultivos()
    {
        return $this->belongsToMany(Cultivo::class, 'estado_cultivo');
    }
}
