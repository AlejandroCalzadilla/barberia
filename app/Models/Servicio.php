<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Categoria;
use App\Models\Producto;

class Servicio extends Model
{
    use HasFactory;

    protected $table = 'services';
    protected $primaryKey = 'id_servicio';

    protected $fillable = [
        'nombre',
        'descripcion',
        'duracion_minutos',
        'precio',
        'estado',
        'imagen',
    ];

    /**
     * The productos that belong to the servicio.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'service_product', 'id_servicio', 'id_producto')
            ->withPivot('cantidad')
            ->withTimestamps();
    }

    /**
     * Get the reservas for the servicio.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'id_servicio', 'id_servicio');
    }
}
