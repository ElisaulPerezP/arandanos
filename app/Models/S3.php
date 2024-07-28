<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class S3 extends Model
{
    use HasFactory;

    protected $table = 's3';

    protected $fillable = [
        'estado',
        'comando_id',
        'pump1',
        'pump2',
    ];

    public function comando()
    {
        return $this->belongsTo(ComandoHardware::class);
    }
}
