<?php

namespace Database\Seeders;

use App\Models\Rating; // Asegurate de tener este modelo
use App\Models\Technician;
use App\Models\TechnicianRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class RatingSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Obtenemos técnicos y solicitudes REALES de la base de datos
        // Usamos 'has' para asegurarnos de que la request tenga un técnico asignado
        $requests = TechnicianRequest::whereNotNull('technician_id')->get();
        
        if ($requests->isEmpty()) {
            $this->command->info('No hay solicitudes de servicio completadas para calificar.');
            return;
        }

        // 2. Recorremos algunas solicitudes para agregarles review
        // (No a todas, para que parezca real)
        foreach ($requests as $request) {
            
            // Simulamos un 70% de probabilidad de que el cliente deje review
            if (rand(1, 100) > 30) {
                
                Rating::create([
                    // La review pertenece a la solicitud
                    'technician_request_id' => $request->id,
                    
                    // El técnico a calificar es el mismo de la solicitud
                    'technician_id' => $request->technician_id,
                    
                    // El usuario que califica es el creador de la solicitud
                    'user_id' => $request->requesting_user_id,
                    
                    // Datos de la review
                    'score' => rand(3, 5), // Calificaciones entre 3 y 5
                    'description' => $this->getRandomComment(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('RatingSeeder ejecutado exitosamente.');
    }

    private function getRandomComment(): string
    {
        $comments = [
            'Excelente trabajo, muy puntual.',
            'El técnico fue amable pero llegó un poco tarde.',
            'Muy profesional, resolvió el problema rápido.',
            'Todo bien, recomendable.',
            'El precio me pareció justo y el trabajo quedó bien.',
            'Explicó todo claramente antes de empezar.',
        ];

        return $comments[array_rand($comments)];
    }
}