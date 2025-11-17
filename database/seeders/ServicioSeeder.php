<?php

namespace Database\Seeders;

use App\Models\Servicio;
use Illuminate\Database\Seeder;

class ServicioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('→ Creando servicios...');
        
        $servicios = [
            [
                'nombre' => 'Corte de Cabello Clásico',
                'descripcion' => 'Corte de cabello tradicional con tijera y máquina, incluye lavado y secado.',
                'duracion_minutos' => 30,
                'precio' => 15.00,
                'estado' => 'activo',
                'imagen' => 'https://example.com/images/corte-clasico.jpg'
            ],
            [
                'nombre' => 'Corte Fade/Degradado',
                'descripcion' => 'Corte moderno con degradado (fade) bajo, medio o alto según preferencia del cliente.',
                'duracion_minutos' => 45,
                'precio' => 20.00,
                'estado' => 'activo',
                'imagen' => 'https://example.com/images/fade.jpg'
            ],
            [
                'nombre' => 'Afeitado Clásico con Navaja',
                'descripcion' => 'Afeitado tradicional con navaja, incluye toallas calientes y bálsamo aftershave.',
                'duracion_minutos' => 30,
                'precio' => 12.00,
                'estado' => 'activo',
                'imagen' => 'https://example.com/images/afeitado.jpg'
            ],
            [
                'nombre' => 'Arreglo de Barba',
                'descripcion' => 'Perfilado y recorte de barba, incluye aceite hidratante para barba.',
                'duracion_minutos' => 20,
                'precio' => 10.00,
                'estado' => 'activo',
                'imagen' => 'https://example.com/images/barba.jpg'
            ],
            [
                'nombre' => 'Corte Infantil',
                'descripcion' => 'Corte de cabello para niños (hasta 12 años), ambiente amigable y rápido.',
                'duracion_minutos' => 25,
                'precio' => 10.00,
                'estado' => 'activo',
                'imagen' => 'https://example.com/images/corte-infantil.jpg'
            ],
            [
                'nombre' => 'Combo Corte + Barba',
                'descripcion' => 'Servicio completo: corte de cabello + arreglo de barba, ideal para un cambio total de look.',
                'duracion_minutos' => 60,
                'precio' => 25.00,
                'estado' => 'activo',
                'imagen' => 'https://example.com/images/combo.jpg'
            ],
            [
                'nombre' => 'Diseño de Cabello',
                'descripcion' => 'Diseños artísticos con máquina en el cabello (líneas, figuras, patrones).',
                'duracion_minutos' => 40,
                'precio' => 18.00,
                'estado' => 'activo',
                'imagen' => 'https://example.com/images/diseno-cabello.jpg'
            ]
        ];

        $count = 0;
        foreach ($servicios as $servicio) {
            try {
                Servicio::create($servicio);
                $count++;
                $this->command->info("✓ Servicio creado: {$servicio['nombre']}");
            } catch (\Exception $e) {
                $this->command->error("✗ Error creando servicio {$servicio['nombre']}: " . $e->getMessage());
            }
        }

        $this->command->info("→ Total de servicios creados: {$count}");
    }
}
