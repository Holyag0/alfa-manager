<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FinancialTransaction;
use App\Models\FinancialCategory;
use Carbon\Carbon;

class FinancialTransactionSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        // Buscar categorias existentes
        $receitaCategories = FinancialCategory::where('type', 'receita')->where('is_active', true)->get();
        $despesaCategories = FinancialCategory::where('type', 'despesa')->where('is_active', true)->get();

        if ($receitaCategories->isEmpty() || $despesaCategories->isEmpty()) {
            $this->command->error('É necessário ter categorias cadastradas. Execute primeiro o FinancialCategorySeeder.');
            return;
        }

        // RECEITAS MANUAIS (não rastreadas)
        $receitas = [
            [
                'description' => 'Venda de materiais didáticos excedentes',
                'amount' => 850.00,
                'transaction_date' => Carbon::now()->subDays(15),
                'payment_method' => 'pix',
                'payer_name' => 'Maria Eduarda Santos',
                'payer_document' => '123.456.789-01',
                'reference' => 'NF-2024-001',
                'notes' => 'Venda de apostilas e livros do semestre anterior',
            ],
            [
                'description' => 'Doação para reforma da biblioteca',
                'amount' => 2500.00,
                'transaction_date' => Carbon::now()->subDays(10),
                'payment_method' => 'bank_transfer',
                'payer_name' => 'Associação de Pais e Mestres',
                'payer_document' => '12.345.678/0001-90',
                'reference' => 'DOC-00123456',
                'notes' => 'Doação para compra de novos livros e móveis',
            ],
            [
                'description' => 'Receita de aluguel de espaço para evento',
                'amount' => 1200.00,
                'transaction_date' => Carbon::now()->subDays(8),
                'payment_method' => 'cash',
                'payer_name' => 'José Carlos Oliveira',
                'payer_document' => '987.654.321-00',
                'reference' => null,
                'notes' => 'Aluguel do auditório para evento empresarial no sábado',
            ],
            [
                'description' => 'Venda de uniformes escolares',
                'amount' => 450.00,
                'transaction_date' => Carbon::now()->subDays(5),
                'payment_method' => 'credit_card',
                'payer_name' => 'Ana Paula Ferreira',
                'payer_document' => '456.789.123-45',
                'reference' => 'CC-789456',
                'notes' => null,
            ],
            [
                'description' => 'Receita de curso extra-curricular de robótica',
                'amount' => 600.00,
                'transaction_date' => Carbon::now()->subDays(3),
                'payment_method' => 'pix',
                'payer_name' => null, // Sem informação do pagador
                'payer_document' => null,
                'reference' => null,
                'notes' => 'Pagamento avulso recebido',
            ],
            [
                'description' => 'Receita de fotocópias e impressões',
                'amount' => 180.50,
                'transaction_date' => Carbon::now()->subDays(2),
                'payment_method' => 'cash',
                'payer_name' => 'Diversos alunos',
                'payer_document' => null,
                'reference' => null,
                'notes' => 'Receita acumulada da semana',
            ],
        ];

        foreach ($receitas as $receita) {
            $category = $receitaCategories->random();
            
            FinancialTransaction::create([
                'transaction_number' => $this->generateTransactionNumber('REC'),
                'type' => 'receita',
                'category_id' => $category->id,
                'source_type' => null, // Não rastreada
                'source_id' => null,
                'description' => $receita['description'],
                'amount' => $receita['amount'],
                'transaction_date' => $receita['transaction_date'],
                'payment_method' => $receita['payment_method'],
                'reference' => $receita['reference'],
                'notes' => $receita['notes'],
                'status' => 'confirmed',
                'confirmed_at' => $receita['transaction_date'],
                'confirmed_by' => 1, // Assumindo admin com ID 1
                'payer_name' => $receita['payer_name'],
                'payer_document' => $receita['payer_document'],
            ]);
        }

        // DESPESAS MANUAIS (não rastreadas)
        $despesas = [
            [
                'description' => 'Pagamento de energia elétrica - Janeiro/2024',
                'amount' => 3850.00,
                'transaction_date' => Carbon::now()->subDays(12),
                'payment_method' => 'bank_transfer',
                'payee_name' => 'Companhia Energética do Estado',
                'payee_document' => '23.456.789/0001-12',
                'reference' => 'Fatura 202401-78945',
                'notes' => 'Consumo referente ao mês de janeiro',
            ],
            [
                'description' => 'Compra de material de limpeza',
                'amount' => 680.00,
                'transaction_date' => Carbon::now()->subDays(10),
                'payment_method' => 'credit_card',
                'payee_name' => 'Distribuidora Limpe Bem Ltda',
                'payee_document' => '34.567.890/0001-23',
                'reference' => 'NF 4567',
                'notes' => 'Compra mensal de produtos de limpeza e higiene',
            ],
            [
                'description' => 'Manutenção do ar condicionado',
                'amount' => 1200.00,
                'transaction_date' => Carbon::now()->subDays(9),
                'payment_method' => 'pix',
                'payee_name' => 'João Silva Refrigeração ME',
                'payee_document' => '45.678.901/0001-34',
                'reference' => 'OS-2024-156',
                'notes' => 'Manutenção preventiva de 8 aparelhos',
            ],
            [
                'description' => 'Pagamento de contador',
                'amount' => 1800.00,
                'transaction_date' => Carbon::now()->subDays(7),
                'payment_method' => 'bank_transfer',
                'payee_name' => 'Contabilidade Exata S/S',
                'payee_document' => '56.789.012/0001-45',
                'reference' => 'Recibo 024/2024',
                'notes' => 'Serviços contábeis - Janeiro/2024',
            ],
            [
                'description' => 'Compra de materiais didáticos',
                'amount' => 2450.00,
                'transaction_date' => Carbon::now()->subDays(6),
                'payment_method' => 'credit_card',
                'payee_name' => 'Livraria Escolar Premium',
                'payee_document' => '67.890.123/0001-56',
                'reference' => 'NF 89456',
                'notes' => 'Livros, apostilas e materiais para novo semestre',
            ],
            [
                'description' => 'Internet banda larga - Janeiro/2024',
                'amount' => 350.00,
                'transaction_date' => Carbon::now()->subDays(5),
                'payment_method' => 'debit_card',
                'payee_name' => 'Provedor Internet Rápida',
                'payee_document' => '78.901.234/0001-67',
                'reference' => 'Fatura 012024',
                'notes' => null,
            ],
            [
                'description' => 'Manutenção do jardim e áreas verdes',
                'amount' => 450.00,
                'transaction_date' => Carbon::now()->subDays(4),
                'payment_method' => 'cash',
                'payee_name' => 'Pedro Jardins e Paisagismo',
                'payee_document' => '234.567.890-12',
                'reference' => null,
                'notes' => 'Serviço mensal de jardinagem',
            ],
            [
                'description' => 'Impressão de certificados e diplomas',
                'amount' => 890.00,
                'transaction_date' => Carbon::now()->subDays(3),
                'payment_method' => 'pix',
                'payee_name' => null, // Sem informação do beneficiário
                'payee_document' => null,
                'reference' => null,
                'notes' => 'Gráfica não identificada - pagamento avulso',
            ],
            [
                'description' => 'Taxa de licenciamento de software educacional',
                'amount' => 1500.00,
                'transaction_date' => Carbon::now()->subDays(2),
                'payment_method' => 'bank_transfer',
                'payee_name' => 'EduTech Software Solutions',
                'payee_document' => '89.012.345/0001-78',
                'reference' => 'Invoice INV-2024-0045',
                'notes' => 'Renovação anual da licença - 50 usuários',
            ],
            [
                'description' => 'Compra de equipamentos de segurança',
                'amount' => 3200.00,
                'transaction_date' => Carbon::now()->subDay(),
                'payment_method' => 'credit_card',
                'payee_name' => 'Segurança Total Equipamentos',
                'payee_document' => '90.123.456/0001-89',
                'reference' => 'NF 56789',
                'notes' => 'Câmeras de segurança e sistema de alarme',
            ],
        ];

        foreach ($despesas as $despesa) {
            $category = $despesaCategories->random();
            
            FinancialTransaction::create([
                'transaction_number' => $this->generateTransactionNumber('DESP'),
                'type' => 'despesa',
                'category_id' => $category->id,
                'source_type' => null, // Não rastreada
                'source_id' => null,
                'description' => $despesa['description'],
                'amount' => $despesa['amount'],
                'transaction_date' => $despesa['transaction_date'],
                'payment_method' => $despesa['payment_method'],
                'reference' => $despesa['reference'],
                'notes' => $despesa['notes'],
                'status' => 'confirmed',
                'confirmed_at' => $despesa['transaction_date'],
                'confirmed_by' => 1, // Assumindo admin com ID 1
                'payee_name' => $despesa['payee_name'],
                'payee_document' => $despesa['payee_document'],
            ]);
        }

        // Adicionar algumas transações PENDENTES
        $pendentes = [
            [
                'type' => 'receita',
                'description' => 'Receita aguardando confirmação',
                'amount' => 500.00,
                'status' => 'pending',
                'payer_name' => 'Cliente Pendente',
                'payer_document' => '111.222.333-44',
            ],
            [
                'type' => 'despesa',
                'description' => 'Despesa aguardando pagamento',
                'amount' => 750.00,
                'status' => 'pending',
                'payee_name' => 'Fornecedor ABC',
                'payee_document' => '11.222.333/0001-44',
            ],
        ];

        foreach ($pendentes as $pendente) {
            $category = $pendente['type'] === 'receita' 
                ? $receitaCategories->random() 
                : $despesaCategories->random();
            
            $data = [
                'transaction_number' => $this->generateTransactionNumber($pendente['type'] === 'receita' ? 'REC' : 'DESP'),
                'type' => $pendente['type'],
                'category_id' => $category->id,
                'source_type' => null,
                'source_id' => null,
                'description' => $pendente['description'],
                'amount' => $pendente['amount'],
                'transaction_date' => Carbon::now(),
                'payment_method' => 'pix',
                'reference' => null,
                'notes' => 'Transação pendente de confirmação',
                'status' => 'pending',
                'confirmed_at' => null,
                'confirmed_by' => null,
            ];

            if ($pendente['type'] === 'receita') {
                $data['payer_name'] = $pendente['payer_name'];
                $data['payer_document'] = $pendente['payer_document'];
            } else {
                $data['payee_name'] = $pendente['payee_name'];
                $data['payee_document'] = $pendente['payee_document'];
            }

            FinancialTransaction::create($data);
        }

        $this->command->info('✅ Transações de exemplo criadas com sucesso!');
        $this->command->info('   - 6 Receitas manuais (não rastreadas)');
        $this->command->info('   - 10 Despesas manuais (não rastreadas)');
        $this->command->info('   - 2 Transações pendentes');
        $this->command->info('   Total: 18 transações criadas');
    }

    /**
     * Gerar número único de transação
     * Usa o mesmo formato do FinancialService para consistência
     * Formato: PREFIX-YEAR-MONTH-NUMBER (ex: REC-2025-12-00001)
     */
    private function generateTransactionNumber(string $prefix): string
    {
        $now = now();
        $year = $now->year;
        $month = $now->format('m');
        
        // Buscar último número sequencial do mês atual
        $lastTransaction = FinancialTransaction::whereYear('created_at', $year)
            ->whereMonth('created_at', $now->month)
            ->where('transaction_number', 'like', "{$prefix}-{$year}-{$month}-%")
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastTransaction) {
            $parts = explode('-', $lastTransaction->transaction_number);
            if (count($parts) >= 4) {
                $lastNumber = intval($parts[3] ?? '0');
                $nextNumber = $lastNumber > 0 ? $lastNumber + 1 : 1;
            }
        }
        
        return "{$prefix}-{$year}-{$month}-" . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }
}

