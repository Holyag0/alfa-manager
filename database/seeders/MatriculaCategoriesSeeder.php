<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class MatriculaCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar categoria para matrícula-reserva
        Category::firstOrCreate(
            ['name' => 'matricula-reserva'],
            [
                'description' => 'Serviços de reserva de matrícula',
                'type' => 'service',
                'color' => '#FF6B6B',
                'is_active' => true
            ]
        );

        // Criar categoria para matrícula normal
        Category::firstOrCreate(
            ['name' => 'matricula'],
            [
                'description' => 'Serviços de matrícula normal',
                'type' => 'service',
                'color' => '#4ECDC4',
                'is_active' => true
            ]
        );

        $this->command->info('Categorias de matrícula criadas com sucesso!');
    }
}
