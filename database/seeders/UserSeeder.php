<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Technician;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin
        User::firstOrCreate(
            ['email' => 'admin@sicac.com'],
            [
                'name' => 'Admin Sicac',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'dni' => '00000000A',
                'phone' => '555-0001',
                'address' => 'Admin Street 1',
                'city' => 'Admin City',
            ]
        );

        $this->command->info('Admin user created: admin@sicac.com / admin123');

        // Create regular users
        $regularUsers = [
            [
                'name' => 'Juan García',
                'email' => 'juan@example.com',
                'dni' => '12345678A',
                'phone' => '555-1001',
                'address' => 'Calle Primera 10',
                'city' => 'Madrid',
            ],
            [
                'name' => 'María López',
                'email' => 'maria@example.com',
                'dni' => '23456789B',
                'phone' => '555-1002',
                'address' => 'Calle Segunda 20',
                'city' => 'Barcelona',
            ],
            [
                'name' => 'Carlos Rodríguez',
                'email' => 'carlos@example.com',
                'dni' => '34567890C',
                'phone' => '555-1003',
                'address' => 'Calle Tercera 30',
                'city' => 'Valencia',
            ],
            [
                'name' => 'Ana Martínez',
                'email' => 'ana@example.com',
                'dni' => '45678901D',
                'phone' => '555-1004',
                'address' => 'Calle Cuarta 40',
                'city' => 'Sevilla',
            ],
        ];

        foreach ($regularUsers as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, [
                    'password' => Hash::make('password123'),
                    'role' => 'user',
                ])
            );

            $this->command->info("Regular user created: {$userData['email']} / password123");
        }

        // Create technicians
        $technicianUsers = [
            [
                'name' => 'Pedro García Técnico',
                'email' => 'pedro.tech@example.com',
                'dni' => '56789012E',
                'phone' => '555-2001',
                'address' => 'Avenida Técnicos 100',
                'city' => 'Madrid',
            ],
            [
                'name' => 'Sofia López Técnico',
                'email' => 'sofia.tech@example.com',
                'dni' => '67890123F',
                'phone' => '555-2002',
                'address' => 'Avenida Técnicos 200',
                'city' => 'Barcelona',
            ],
            [
                'name' => 'Roberto Sánchez Técnico',
                'email' => 'roberto.tech@example.com',
                'dni' => '78901234G',
                'phone' => '555-2003',
                'address' => 'Avenida Técnicos 300',
                'city' => 'Valencia',
            ],
        ];

        foreach ($technicianUsers as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, [
                    'password' => Hash::make('password123'),
                    'role' => 'technician',
                ])
            );

            // Create technician profile if it doesn't exist
            Technician::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'availability_date' => now()->addDay(),
                ]
            );

            $this->command->info("Technician user created: {$userData['email']} / password123");
        }
    }
}
