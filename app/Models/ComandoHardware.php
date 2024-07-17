<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComandoHardware extends Model
{
    use HasFactory;

    protected $table = 'comando_hardware';

    protected $fillable = [
        'sistema',
        'comando',
    ];

    public function s0()
    {
        return $this->hasMany(S0::class, 'comando_id');
    }

    public function s1()
    {
        return $this->hasMany(S1::class, 'comando_id');
    }

    public function s2()
    {
        return $this->hasMany(S2::class, 'comando_id');
    }

    public function s3()
    {
        return $this->hasMany(S3::class, 'comando_id');
    }

    public function s4()
    {
        return $this->hasMany(S4::class, 'comando_id');
    }

    public function s5()
    {
        return $this->hasMany(S5::class, 'comando_id');
    }
}
