<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpar tabela antes de popular (sem foreign keys ativas)
        Supplier::query()->delete();

        // Pagantes (para receitas)
        $pagantes = [
            [
                'is_pagante' => true,
                'is_fornecedor' => false,
                'name' => 'Maria Eduarda Santos',
                'document' => '123.456.789-01',
                'email' => 'maria.santos@email.com',
                'phone' => '(11) 98765-4321',
                'address' => 'Rua das Flores, 123 - São Paulo/SP',
                'notes' => 'Responsável pela matrícula do aluno João Silva',
                'active' => true,
            ],
            [
                'is_pagante' => true,
                'is_fornecedor' => false,
                'name' => 'José Carlos Oliveira',
                'document' => '987.654.321-00',
                'email' => 'jose.oliveira@email.com',
                'phone' => '(11) 91234-5678',
                'address' => 'Av. Paulista, 1000 - São Paulo/SP',
                'notes' => 'Pai da aluna Ana Oliveira',
                'active' => true,
            ],
            [
                'is_pagante' => true,
                'is_fornecedor' => false,
                'name' => 'Ana Paula Ferreira',
                'document' => '456.789.123-45',
                'email' => 'ana.ferreira@email.com',
                'phone' => '(11) 99876-5432',
                'address' => 'Rua Augusta, 500 - São Paulo/SP',
                'notes' => 'Mãe do aluno Pedro Ferreira',
                'active' => true,
            ],
            [
                'is_pagante' => true,
                'is_fornecedor' => false,
                'name' => 'Roberto Almeida',
                'document' => '321.654.987-12',
                'email' => 'roberto.almeida@email.com',
                'phone' => '(11) 97654-3210',
                'address' => 'Rua Consolação, 789 - São Paulo/SP',
                'notes' => null,
                'active' => true,
            ],
            [
                'is_pagante' => true,
                'is_fornecedor' => false,
                'name' => 'Associação de Pais e Mestres',
                'document' => '12.345.678/0001-90',
                'email' => 'contato@apm-escola.com.br',
                'phone' => '(11) 3456-7890',
                'address' => 'Rua da Escola, 50 - São Paulo/SP',
                'notes' => 'Associação que organiza eventos e arrecadações',
                'active' => true,
            ],
            [
                'is_pagante' => true,
                'is_fornecedor' => false,
                'name' => 'Carlos Eduardo Lima',
                'document' => '789.123.456-78',
                'email' => 'carlos.lima@email.com',
                'phone' => '(11) 92345-6789',
                'address' => 'Rua dos Estudantes, 200 - São Paulo/SP',
                'notes' => null,
                'active' => true,
            ],
            [
                'is_pagante' => true,
                'is_fornecedor' => false,
                'name' => 'Patrícia Souza',
                'document' => '654.321.987-65',
                'email' => 'patricia.souza@email.com',
                'phone' => '(11) 93456-7890',
                'address' => 'Av. Faria Lima, 1500 - São Paulo/SP',
                'notes' => 'Responsável por múltiplas matrículas',
                'active' => true,
            ],
        ];

        // Fornecedores (para despesas)
        $fornecedores = [
            [
                'is_pagante' => false,
                'is_fornecedor' => true,
                'name' => 'Companhia Energética do Estado',
                'document' => '23.456.789/0001-12',
                'email' => 'faturamento@energia.com.br',
                'phone' => '(11) 3000-0000',
                'address' => 'Av. das Energias, 1000 - São Paulo/SP',
                'notes' => 'Fornecedor de energia elétrica',
                'active' => true,
            ],
            [
                'is_pagante' => false,
                'is_fornecedor' => true,
                'name' => 'Distribuidora Limpe Bem Ltda',
                'document' => '34.567.890/0001-23',
                'email' => 'vendas@limpebem.com.br',
                'phone' => '(11) 3456-7890',
                'address' => 'Rua dos Produtos, 500 - São Paulo/SP',
                'notes' => 'Fornecedor de material de limpeza',
                'active' => true,
            ],
            [
                'is_pagante' => false,
                'is_fornecedor' => true,
                'name' => 'João Silva Refrigeração ME',
                'document' => '45.678.901/0001-34',
                'email' => 'contato@refrigeracaojs.com.br',
                'phone' => '(11) 98765-4321',
                'address' => 'Rua do Gelo, 200 - São Paulo/SP',
                'notes' => 'Manutenção de ar condicionado',
                'active' => true,
            ],
            [
                'is_pagante' => false,
                'is_fornecedor' => true,
                'name' => 'Contabilidade Exata S/S',
                'document' => '56.789.012/0001-45',
                'email' => 'contato@exata.com.br',
                'phone' => '(11) 3456-7890',
                'address' => 'Av. Contadores, 300 - São Paulo/SP',
                'notes' => 'Serviços contábeis e fiscais',
                'active' => true,
            ],
            [
                'is_pagante' => false,
                'is_fornecedor' => true,
                'name' => 'Livraria Escolar Premium',
                'document' => '67.890.123/0001-56',
                'email' => 'pedidos@livrariapremium.com.br',
                'phone' => '(11) 3456-7890',
                'address' => 'Rua dos Livros, 150 - São Paulo/SP',
                'notes' => 'Fornecedor de material didático e livros',
                'active' => true,
            ],
            [
                'is_pagante' => false,
                'is_fornecedor' => true,
                'name' => 'Provedor Internet Rápida',
                'document' => '78.901.234/0001-67',
                'email' => 'comercial@internetrapida.com.br',
                'phone' => '(11) 3000-1234',
                'address' => 'Av. Tecnologia, 800 - São Paulo/SP',
                'notes' => 'Fornecedor de internet e telefonia',
                'active' => true,
            ],
            [
                'is_pagante' => false,
                'is_fornecedor' => true,
                'name' => 'Pedro Jardins e Paisagismo',
                'document' => '234.567.890-12',
                'email' => 'pedro@jardins.com.br',
                'phone' => '(11) 91234-5678',
                'address' => 'Rua das Plantas, 50 - São Paulo/SP',
                'notes' => 'Manutenção de jardins e áreas verdes',
                'active' => true,
            ],
            [
                'is_pagante' => false,
                'is_fornecedor' => true,
                'name' => 'EduTech Software Solutions',
                'document' => '89.012.345/0001-78',
                'email' => 'vendas@edutech.com.br',
                'phone' => '(11) 3456-7890',
                'address' => 'Av. Software, 2000 - São Paulo/SP',
                'notes' => 'Sistemas e softwares educacionais',
                'active' => true,
            ],
            [
                'is_pagante' => false,
                'is_fornecedor' => true,
                'name' => 'Segurança Total Equipamentos',
                'document' => '90.123.456/0001-89',
                'email' => 'comercial@segurancatotal.com.br',
                'phone' => '(11) 3456-7890',
                'address' => 'Rua da Segurança, 100 - São Paulo/SP',
                'notes' => 'Equipamentos de segurança e monitoramento',
                'active' => true,
            ],
            [
                'is_pagante' => false,
                'is_fornecedor' => true,
                'name' => 'Cantina Sabor & Qualidade',
                'document' => '01.234.567/0001-90',
                'email' => 'pedidos@cantinasabor.com.br',
                'phone' => '(11) 3456-7890',
                'address' => 'Rua da Cantina, 25 - São Paulo/SP',
                'notes' => 'Fornecedor de alimentos e lanches',
                'active' => true,
            ],
            [
                'is_pagante' => false,
                'is_fornecedor' => true,
                'name' => 'Transporte Escolar Seguro',
                'document' => '12.345.678/0001-01',
                'email' => 'contato@transporteescolar.com.br',
                'phone' => '(11) 3456-7890',
                'address' => 'Av. dos Transportes, 600 - São Paulo/SP',
                'notes' => 'Serviço de transporte escolar',
                'active' => true,
            ],
            [
                'is_pagante' => false,
                'is_fornecedor' => true,
                'name' => 'Impressão Rápida Gráfica',
                'document' => '23.456.789/0001-12',
                'email' => 'pedidos@impressaorapida.com.br',
                'phone' => '(11) 3456-7890',
                'address' => 'Rua das Impressões, 300 - São Paulo/SP',
                'notes' => 'Serviços de impressão e gráfica',
                'active' => true,
            ],
        ];

        // Inserir pagantes
        foreach ($pagantes as $pagante) {
            Supplier::create($pagante);
        }

        // Inserir fornecedores
        foreach ($fornecedores as $fornecedor) {
            Supplier::create($fornecedor);
        }

        $this->command->info('Fornecedores e Pagantes criados com sucesso!');
        $this->command->info('Total: ' . count($pagantes) . ' pagantes e ' . count($fornecedores) . ' fornecedores.');
    }
}
