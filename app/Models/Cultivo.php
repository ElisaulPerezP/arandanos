<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Cultivo extends Model
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'nombre', 'coordenadas', 'estado_id', 'comando_id', 'api_token',
    ];

    public function estadoActual()
    {
        return $this->belongsTo(Estado::class, 'estado_id');
    }

    public function comandoActual()
    {
        return $this->belongsTo(Comando::class, 'comando_id');
    }

    public function estados()
    {
        return $this->belongsToMany(Estado::class, 'estado_cultivo');
    }

    public function ultimaConexion()
    {
        // Retorna la última conexión basada en la fecha_unix más reciente
        return $this->hasOne(Conexion::class)->latestOfMany('fecha_unix');
    }

    public function conexiones()
    {
        return $this->hasMany(Conexion::class);
    }

    public function programaciones()
    {
        return $this->hasMany(Programacion::class);
    }

    public function mensajes()
    {
        return $this->hasMany(Mensaje::class);
    }

    public function comandos()
    {
        return $this->belongsToMany(Comando::class, 'cultivo_comando'); // Especifica el nombre correcto de la tabla pivot
    }

    public function getUltimasConexiones()
    {
        return $this->conexiones()->latest('fecha_unix')->limit(10)->get();
    }
}
