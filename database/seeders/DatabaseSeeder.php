<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Seed des rôles par défaut
        $this->call([
            RoleSeeder::class,
        ]);

        // Seed des types de garanties
        $this->call([
            TypeGarantieSeeder::class,
        ]);

        // Seed des clients et contrats de prêts (données de test)
        $this->call([
            ClientSeeder::class,
            ContratPretSeeder::class,
        ]);
    }
}
