<?php

namespace Database\Seeders;

use App\Models\TechnicianRequest;
use App\Models\User;
use App\Models\Category;
use App\Models\Technician;
use Illuminate\Database\Seeder;

class TechnicianRequestSeeder extends Seeder
{
    public function run(): void
    {
        // Get regular users (requesters) and technicians
        $requesters = User::where('role', 'user')->get();
        $technicians = Technician::with('user')->get();
        $categories = Category::limit(3)->get();

        if ($requesters->isEmpty() || $technicians->isEmpty() || $categories->isEmpty()) {
            $this->command->info('Missing data (users, technicians, or categories). Skipping technician requests seeder.');
            return;
        }

        $requests = [
            [
                'subject' => 'Instalación de aire acondicionado',
                'description' => 'Necesito instalar un aire acondicionado en la sala de estar.',
                'wanted_date_start' => now()->addDays(2)->toDateString(),
                'wanted_date_end' => now()->addDays(3)->toDateString(),
                'time_shift' => 'morning',
            ],
            [
                'subject' => 'Reparación de calefacción',
                'description' => 'La calefacción central no funciona correctamente.',
                'wanted_date_start' => now()->addDays(1)->toDateString(),
                'wanted_date_end' => now()->addDays(1)->toDateString(),
                'time_shift' => 'afternoon',
            ],
            [
                'subject' => 'Instalación de ventilación',
                'description' => 'Quiero instalar un sistema de ventilación en la cocina.',
                'wanted_date_start' => now()->addDays(5)->toDateString(),
                'wanted_date_end' => now()->addDays(6)->toDateString(),
                'time_shift' => 'morning',
            ],
            [
                'subject' => 'Me destapo la sanjaaaa',
                'description' => 'Ai ai se eu te pego vai ser uma confusão',
                'wanted_date_start' => now()->addDays(3)->toDateString(),
                'wanted_date_end' => now()->addDays(3)->toDateString(),
                'time_shift' => 'afternoon',
            ],
            [
                'subject' => 'Inspección y limpieza de conductos',
                'description' => 'Limpieza y revisión de los conductos de aire.',
                'wanted_date_start' => now()->addDays(7)->toDateString(),
                'wanted_date_end' => now()->addDays(7)->toDateString(),
                'time_shift' => 'morning',
            ],
        ];

        foreach ($requests as $index => $requestData) {
            $requester = $requesters->get($index % $requesters->count());
            $technician = $technicians->get($index % $technicians->count());
            $category = $categories->get($index % $categories->count());

            TechnicianRequest::firstOrCreate(
                [
                    'requesting_user_id' => $requester->id,
                    'subject' => $requestData['subject'],
                ],
                [
                    'technician_id' => $technician->id,
                    'category_id' => $category->id,
                    'description' => $requestData['description'],
                    'wanted_date_start' => $requestData['wanted_date_start'],
                    'wanted_date_end' => $requestData['wanted_date_end'],
                    'time_shift' => $requestData['time_shift'],
                ]
            );

            $this->command->info("Technician request created: {$requestData['subject']}");
        }
    }
}
