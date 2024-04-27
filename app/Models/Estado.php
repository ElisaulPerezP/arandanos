<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    use HasFactory;

    protected $fillable = [
        'solenoide_1', 'solenoide_2', 'solenoide_3', 'solenoide_4',
        'solenoide_5', 'solenoide_6', 'solenoide_7', 'solenoide_8',
        'solenoide_9', 'solenoide_10', 'solenoide_11', 'solenoide_12',
        'bomba_1', 'bomba_2', 'bomba_fertilizante', 'id_tabla_flujos'
    ];

    public function flujos()
    {
        return $this->hasMany(Flujo::class, 'estado_id');
    }
}
