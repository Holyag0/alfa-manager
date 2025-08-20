<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // Categorias para Educação Infantil
            [
                'name' => 'Educação Infantil',
                'description' => 'Serviços e pacotes relacionados à educação infantil',
                'type' => 'both',
                'color' => '#3B82F6',
                'is_active' => true,
            ],
            [
                'name' => 'Berçário',
                'description' => 'Serviços específicos para berçário',
                'type' => 'service',
                'color' => '#10B981',
                'is_active' => true,
            ],
            [
                'name' => 'Maternal',
                'description' => 'Serviços específicos para maternal',
                'type' => 'service',
                'color' => '#F59E0B',
                'is_active' => true,
            ],
            [
                'name' => 'Jardim I',
                'description' => 'Serviços específicos para jardim I',
                'type' => 'service',
                'color' => '#8B5CF6',
                'is_active' => true,
            ],
            [
                'name' => 'Jardim II',
                'description' => 'Serviços específicos para jardim II',
                'type' => 'service',
                'color' => '#EC4899',
                'is_active' => true,
            ],

            // Categorias para Atividades Extracurriculares
            [
                'name' => 'Atividades Extracurriculares',
                'description' => 'Atividades complementares à educação regular',
                'type' => 'both',
                'color' => '#EF4444',
                'is_active' => true,
            ],
            [
                'name' => 'Esportes',
                'description' => 'Atividades esportivas e físicas',
                'type' => 'service',
                'color' => '#06B6D4',
                'is_active' => true,
            ],
            [
                'name' => 'Arte e Cultura',
                'description' => 'Atividades artísticas e culturais',
                'type' => 'service',
                'color' => '#84CC16',
                'is_active' => true,
            ],
            [
                'name' => 'Música',
                'description' => 'Aulas e atividades musicais',
                'type' => 'service',
                'color' => '#F97316',
                'is_active' => true,
            ],
            [
                'name' => 'Dança',
                'description' => 'Aulas de dança e ballet',
                'type' => 'service',
                'color' => '#A855F7',
                'is_active' => true,
            ],

            // Categorias para Serviços Adicionais
            [
                'name' => 'Serviços Adicionais',
                'description' => 'Serviços complementares oferecidos pela escola',
                'type' => 'both',
                'color' => '#6B7280',
                'is_active' => true,
            ],
            [
                'name' => 'Alimentação',
                'description' => 'Serviços de alimentação escolar',
                'type' => 'service',
                'color' => '#059669',
                'is_active' => true,
            ],
            [
                'name' => 'Transporte',
                'description' => 'Serviços de transporte escolar',
                'type' => 'service',
                'color' => '#DC2626',
                'is_active' => true,
            ],
            [
                'name' => 'Material Didático',
                'description' => 'Material escolar e didático',
                'type' => 'service',
                'color' => '#7C3AED',
                'is_active' => true,
            ],
            [
                'name' => 'Período Integral',
                'description' => 'Serviços de período integral',
                'type' => 'service',
                'color' => '#EA580C',
                'is_active' => true,
            ],

            // Categorias para Pacotes Especiais
            [
                'name' => 'Pacotes Especiais',
                'description' => 'Pacotes promocionais e especiais',
                'type' => 'package',
                'color' => '#BE185D',
                'is_active' => true,
            ],
            [
                'name' => 'Pacotes Completos',
                'description' => 'Pacotes que incluem educação + serviços',
                'type' => 'package',
                'color' => '#1D4ED8',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
