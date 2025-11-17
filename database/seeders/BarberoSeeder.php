<?php

namespace Database\Seeders;

use App\Models\Barbero;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BarberoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Datos de ejemplo para barberos
        $barberos = [
            [
                'name' => 'Carlos Mendoza',
                'email' => 'carlos.mendoza@barberia.com',
                'telefono' => '70000003',
                'direccion' => 'Av. Principal 456',
                'especialidad' => 'Cortes clásicos y afeitados tradicionales',
                'foto_perfil' => 'https://example.com/images/barbero1.jpg',
                'estado' => 'disponible'
            ],
            [
                'name' => 'Luis Rojas',
                'email' => 'luis.rojas@barberia.com',
                'telefono' => '70000004',
                'direccion' => 'Calle 10 #25-30',
                'especialidad' => 'Estilos modernos y diseños creativos',
                'foto_perfil' => 'https://example.com/images/barbero2.jpg',
                'estado' => 'disponible'
            ],
            [
                'name' => 'Miguel Ángel',
                'email' => 'miguel.angel@barberia.com',
                'telefono' => '70000005',
                'direccion' => 'Carrera 15 #20-45',
                'especialidad' => 'Especialista en barba y bigote',
                'foto_perfil' => 'https://example.com/images/barbero3.jpg',
                'estado' => 'disponible'
            ]
        ];

        foreach ($barberos as $barberoData) {
            // Crear usuario para el barbero
            $user = User::firstOrCreate(
                ['email' => $barberoData['email']],
                [
                    'name' => $barberoData['name'],
                    'password' => Hash::make('password'),
                    'telefono' => $barberoData['telefono'],
                    'direccion' => $barberoData['direccion'],
                    'estado' => 'activo',
                    'tipo_usuario' => 'barbero',
                    'remember_token' => Str::random(10),
                ]
            );

            // Asignar rol de barbero
            $user->assignRole('barbero');

            // Crear registro en la tabla barbers
            Barbero::create([
                'id_usuario' => $user->id,
                'especialidad' => $barberoData['especialidad'],
                'foto_perfil' => $barberoData['foto_perfil'],
                'estado' => $barberoData['estado'] ?? 'disponible' // Usar el estado definido o 'disponible' por defecto
            ]);
        }
                    

        
    }
}
