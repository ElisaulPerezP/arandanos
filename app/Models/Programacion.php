<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Programacion extends Model
{
    use HasFactory;

    protected $fillable = ['comando_id', 'hora_unix', 'cultivo_id', 'estado'];

    public function cultivo()
    {
        return $this->belongsTo(Cultivo::class);
    }

    public function comando()
    {
        return $this->belongsTo(Comando::class);
    }
}
