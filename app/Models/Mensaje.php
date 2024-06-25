<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mensaje extends Model
{
    use HasFactory;

    protected $fillable = ['contenido', 'cultivo_id'];

    public function cultivo()
    {
        return $this->belongsTo(Cultivo::class);
    }
}
