<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FinancialCategory;

class FinancialCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Receitas
            [
                'name' => 'Mensalidade',
                'type' => 'receita',
                'description' => 'Pagamentos de mensalidades escolares',
                'color' => '#10B981',
                'is_active' => true,
            ],
            [
                'name' => 'Reserva de Matrícula',
                'type' => 'receita',
                'description' => 'Pagamentos de reserva de vaga',
                'color' => '#3B82F6',
                'is_active' => true,
            ],
            [
                'name' => 'Serviços Adicionais',
                'type' => 'receita',
                'description' => 'Pagamentos de serviços extras',
                'color' => '#8B5CF6',
                'is_active' => true,
            ],
            [
                'name' => 'Pacotes',
                'type' => 'receita',
                'description' => 'Pagamentos de pacotes de serviços',
                'color' => '#EC4899',
                'is_active' => true,
            ],
            [
                'name' => 'Outros',
                'type' => 'receita',
                'description' => 'Outras receitas',
                'color' => '#6B7280',
                'is_active' => true,
            ],
            
            // Despesas
            [
                'name' => 'Salários',
                'type' => 'despesa',
                'description' => 'Pagamento de salários e encargos',
                'color' => '#EF4444',
                'is_active' => true,
            ],
            [
                'name' => 'Aluguel',
                'type' => 'despesa',
                'description' => 'Pagamento de aluguel',
                'color' => '#F59E0B',
                'is_active' => true,
            ],
            [
                'name' => 'Água e Luz',
                'type' => 'despesa',
                'description' => 'Contas de água e energia elétrica',
                'color' => '#14B8A6',
                'is_active' => true,
            ],
            [
                'name' => 'Material Escolar',
                'type' => 'despesa',
                'description' => 'Compra de materiais escolares',
                'color' => '#F97316',
                'is_active' => true,
            ],
            [
                'name' => 'Manutenção',
                'type' => 'despesa',
                'description' => 'Manutenção e reparos',
                'color' => '#84CC16',
                'is_active' => true,
            ],
            [
                'name' => 'Alimentação',
                'type' => 'despesa',
                'description' => 'Despesas com alimentação',
                'color' => '#06B6D4',
                'is_active' => true,
            ],
            [
                'name' => 'Outras Despesas',
                'type' => 'despesa',
                'description' => 'Outras despesas operacionais',
                'color' => '#6B7280',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            FinancialCategory::create($category);
        }
    }
}
