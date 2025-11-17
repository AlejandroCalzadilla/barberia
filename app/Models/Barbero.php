<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Barbero extends Model
{
    use HasFactory;

    protected $table = 'barbers';
    protected $primaryKey = 'id_barbero';

    protected $fillable = [
        'id_usuario',
        'especialidad',
        'foto_perfil',
        'calificacion_promedio',
        'estado',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
