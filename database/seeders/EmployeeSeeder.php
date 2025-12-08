<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Support\Str;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar cargos ativos
        $positions = Position::where('is_active', true)->get();

        if ($positions->isEmpty()) {
            $this->command->warn('Nenhum cargo encontrado. Execute o PositionSeeder primeiro.');
            return;
        }

        // Função para gerar CPF válido
        $generateCpf = function() {
            $n1 = rand(0, 9);
            $n2 = rand(0, 9);
            $n3 = rand(0, 9);
            $n4 = rand(0, 9);
            $n5 = rand(0, 9);
            $n6 = rand(0, 9);
            $n7 = rand(0, 9);
            $n8 = rand(0, 9);
            $n9 = rand(0, 9);
            
            $d1 = $n9 * 2 + $n8 * 3 + $n7 * 4 + $n6 * 5 + $n5 * 6 + $n4 * 7 + $n3 * 8 + $n2 * 9 + $n1 * 10;
            $d1 = 11 - ($d1 % 11);
            if ($d1 >= 10) $d1 = 0;
            
            $d2 = $d1 * 2 + $n9 * 3 + $n8 * 4 + $n7 * 5 + $n6 * 6 + $n5 * 7 + $n4 * 8 + $n3 * 9 + $n2 * 10 + $n1 * 11;
            $d2 = 11 - ($d2 % 11);
            if ($d2 >= 10) $d2 = 0;
            
            return sprintf('%d%d%d%d%d%d%d%d%d%d%d', $n1, $n2, $n3, $n4, $n5, $n6, $n7, $n8, $n9, $d1, $d2);
        };

        // Função para gerar RG
        $generateRg = function() {
            return rand(10000000, 99999999) . '-' . rand(0, 9);
        };

        // Função para gerar telefone
        $generatePhone = function() {
            return '(' . rand(11, 99) . ') ' . rand(90000, 99999) . '-' . rand(1000, 9999);
        };

        // Função para gerar endereço
        $generateAddress = function() {
            $streets = ['Rua das Flores', 'Avenida Principal', 'Rua do Comércio', 'Avenida Central', 'Rua da Paz'];
            $neighborhoods = ['Centro', 'Jardim Primavera', 'Vila Nova', 'Bairro Novo', 'Parque das Árvores'];
            $cities = ['São Paulo', 'Rio de Janeiro', 'Belo Horizonte', 'Curitiba', 'Porto Alegre'];
            
            return sprintf(
                '%s, %d - %s, %s - %s, CEP: %d%d%d%d%d-%d%d%d',
                $streets[array_rand($streets)],
                rand(100, 9999),
                $neighborhoods[array_rand($neighborhoods)],
                $cities[array_rand($cities)],
                'SP',
                rand(0, 9), rand(0, 9), rand(0, 9), rand(0, 9), rand(0, 9), rand(0, 9), rand(0, 9), rand(0, 9)
            );
        };

        $employees = [
            // Direção e Administração
            [
                'name' => 'Maria Silva Santos',
                'email' => 'maria.silva@escola.com',
                'phone' => $generatePhone(),
                'cpf' => $generateCpf(),
                'rg' => $generateRg(),
                'birth_date' => '1975-05-15',
                'position_id' => $positions->where('name', 'Diretor')->first()->id ?? $positions->random()->id,
                'hire_date' => '2010-01-15',
                'salary' => 8500.00,
                'address' => $generateAddress(),
                'notes' => 'Diretora com mais de 15 anos de experiência em gestão educacional.',
                'is_active' => true,
            ],
            [
                'name' => 'João Pedro Oliveira',
                'email' => 'joao.oliveira@escola.com',
                'phone' => $generatePhone(),
                'cpf' => $generateCpf(),
                'rg' => $generateRg(),
                'birth_date' => '1980-08-22',
                'position_id' => $positions->where('name', 'Vice-Diretor')->first()->id ?? $positions->random()->id,
                'hire_date' => '2015-03-01',
                'salary' => 7200.00,
                'address' => $generateAddress(),
                'notes' => 'Vice-diretor especializado em gestão administrativa.',
                'is_active' => true,
            ],
            [
                'name' => 'Ana Paula Costa',
                'email' => 'ana.costa@escola.com',
                'phone' => $generatePhone(),
                'cpf' => $generateCpf(),
                'rg' => $generateRg(),
                'birth_date' => '1985-11-10',
                'position_id' => $positions->where('name', 'Coordenador Pedagógico')->first()->id ?? $positions->random()->id,
                'hire_date' => '2018-02-01',
                'salary' => 6500.00,
                'address' => $generateAddress(),
                'notes' => 'Coordenadora pedagógica com especialização em educação infantil.',
                'is_active' => true,
            ],
            [
                'name' => 'Carlos Roberto Lima',
                'email' => 'carlos.lima@escola.com',
                'phone' => $generatePhone(),
                'cpf' => $generateCpf(),
                'rg' => $generateRg(),
                'birth_date' => '1982-03-25',
                'position_id' => $positions->where('name', 'Secretário')->first()->id ?? $positions->random()->id,
                'hire_date' => '2016-06-15',
                'salary' => 3500.00,
                'address' => $generateAddress(),
                'notes' => 'Secretário responsável pela documentação e matrículas.',
                'is_active' => true,
            ],

            // Professores
            [
                'name' => 'Fernanda Alves Pereira',
                'email' => 'fernanda.pereira@escola.com',
                'phone' => $generatePhone(),
                'cpf' => $generateCpf(),
                'rg' => $generateRg(),
                'birth_date' => '1990-07-18',
                'position_id' => $positions->where('name', 'Professor de Educação Infantil')->first()->id ?? $positions->random()->id,
                'hire_date' => '2019-01-10',
                'salary' => 4200.00,
                'address' => $generateAddress(),
                'notes' => 'Professora dedicada com formação em pedagogia.',
                'is_active' => true,
            ],
            [
                'name' => 'Patrícia Souza Martins',
                'email' => 'patricia.martins@escola.com',
                'phone' => $generatePhone(),
                'cpf' => $generateCpf(),
                'rg' => $generateRg(),
                'birth_date' => '1988-12-05',
                'position_id' => $positions->where('name', 'Professor de Berçário')->first()->id ?? $positions->random()->id,
                'hire_date' => '2020-03-01',
                'salary' => 4000.00,
                'address' => $generateAddress(),
                'notes' => 'Professora especializada em berçário com curso de primeiros socorros.',
                'is_active' => true,
            ],
            [
                'name' => 'Roberto Carlos Ferreira',
                'email' => 'roberto.ferreira@escola.com',
                'phone' => $generatePhone(),
                'cpf' => $generateCpf(),
                'rg' => $generateRg(),
                'birth_date' => '1987-04-30',
                'position_id' => $positions->where('name', 'Professor de Música')->first()->id ?? $positions->random()->id,
                'hire_date' => '2018-08-15',
                'salary' => 3800.00,
                'address' => $generateAddress(),
                'notes' => 'Professor de música formado em conservatório.',
                'is_active' => true,
            ],
            [
                'name' => 'Juliana Rodrigues',
                'email' => 'juliana.rodrigues@escola.com',
                'phone' => $generatePhone(),
                'cpf' => $generateCpf(),
                'rg' => $generateRg(),
                'birth_date' => '1992-09-12',
                'position_id' => $positions->where('name', 'Professor de Educação Física')->first()->id ?? $positions->random()->id,
                'hire_date' => '2021-02-01',
                'salary' => 3900.00,
                'address' => $generateAddress(),
                'notes' => 'Professora de educação física com especialização em educação infantil.',
                'is_active' => true,
            ],
            [
                'name' => 'Marcos Antônio Silva',
                'email' => 'marcos.silva@escola.com',
                'phone' => $generatePhone(),
                'cpf' => $generateCpf(),
                'rg' => $generateRg(),
                'birth_date' => '1985-06-20',
                'position_id' => $positions->where('name', 'Professor de Inglês')->first()->id ?? $positions->random()->id,
                'hire_date' => '2017-04-10',
                'salary' => 4100.00,
                'address' => $generateAddress(),
                'notes' => 'Professor de inglês com certificação internacional.',
                'is_active' => true,
            ],

            // Auxiliares
            [
                'name' => 'Lucia Maria Santos',
                'email' => 'lucia.santos@escola.com',
                'phone' => $generatePhone(),
                'cpf' => $generateCpf(),
                'rg' => $generateRg(),
                'birth_date' => '1978-10-08',
                'position_id' => $positions->where('name', 'Auxiliar de Berçário')->first()->id ?? $positions->random()->id,
                'hire_date' => '2015-05-20',
                'salary' => 2200.00,
                'address' => $generateAddress(),
                'notes' => 'Auxiliar experiente no cuidado de bebês.',
                'is_active' => true,
            ],
            [
                'name' => 'Rosa Aparecida Lima',
                'email' => 'rosa.lima@escola.com',
                'phone' => $generatePhone(),
                'cpf' => $generateCpf(),
                'rg' => $generateRg(),
                'birth_date' => '1983-01-15',
                'position_id' => $positions->where('name', 'Auxiliar de Sala')->first()->id ?? $positions->random()->id,
                'hire_date' => '2016-08-01',
                'salary' => 2100.00,
                'address' => $generateAddress(),
                'notes' => 'Auxiliar dedicada e atenciosa.',
                'is_active' => true,
            ],
            [
                'name' => 'José da Silva',
                'email' => 'jose.silva@escola.com',
                'phone' => $generatePhone(),
                'cpf' => $generateCpf(),
                'rg' => $generateRg(),
                'birth_date' => '1975-12-03',
                'position_id' => $positions->where('name', 'Monitor')->first()->id ?? $positions->random()->id,
                'hire_date' => '2014-02-10',
                'salary' => 2300.00,
                'address' => $generateAddress(),
                'notes' => 'Monitor responsável pela supervisão dos alunos.',
                'is_active' => true,
            ],

            // Serviços Gerais
            [
                'name' => 'Maria José Oliveira',
                'email' => 'maria.oliveira@escola.com',
                'phone' => $generatePhone(),
                'cpf' => $generateCpf(),
                'rg' => $generateRg(),
                'birth_date' => '1970-05-25',
                'position_id' => $positions->where('name', 'Cozinheira')->first()->id ?? $positions->random()->id,
                'hire_date' => '2012-01-15',
                'salary' => 2500.00,
                'address' => $generateAddress(),
                'notes' => 'Cozinheira com curso de manipulação de alimentos.',
                'is_active' => true,
            ],
            [
                'name' => 'Antônio Carlos Souza',
                'email' => 'antonio.souza@escola.com',
                'phone' => $generatePhone(),
                'cpf' => $generateCpf(),
                'rg' => $generateRg(),
                'birth_date' => '1968-08-14',
                'position_id' => $positions->where('name', 'Zelador')->first()->id ?? $positions->random()->id,
                'hire_date' => '2011-03-01',
                'salary' => 2400.00,
                'address' => $generateAddress(),
                'notes' => 'Zelador responsável pela manutenção geral.',
                'is_active' => true,
            ],
            [
                'name' => 'Francisco Alves',
                'email' => 'francisco.alves@escola.com',
                'phone' => $generatePhone(),
                'cpf' => $generateCpf(),
                'rg' => $generateRg(),
                'birth_date' => '1972-11-22',
                'position_id' => $positions->where('name', 'Porteiro')->first()->id ?? $positions->random()->id,
                'hire_date' => '2013-06-10',
                'salary' => 2200.00,
                'address' => $generateAddress(),
                'notes' => 'Porteiro responsável pela segurança e controle de acesso.',
                'is_active' => true,
            ],

            // Profissionais Especializados
            [
                'name' => 'Dr. Paulo Henrique Costa',
                'email' => 'paulo.costa@escola.com',
                'phone' => $generatePhone(),
                'cpf' => $generateCpf(),
                'rg' => $generateRg(),
                'birth_date' => '1980-02-18',
                'position_id' => $positions->where('name', 'Psicólogo Escolar')->first()->id ?? $positions->random()->id,
                'hire_date' => '2019-09-01',
                'salary' => 5500.00,
                'address' => $generateAddress(),
                'notes' => 'Psicólogo especializado em desenvolvimento infantil.',
                'is_active' => true,
            ],
            [
                'name' => 'Dra. Camila Rodrigues',
                'email' => 'camila.rodrigues@escola.com',
                'phone' => $generatePhone(),
                'cpf' => $generateCpf(),
                'rg' => $generateRg(),
                'birth_date' => '1985-07-07',
                'position_id' => $positions->where('name', 'Nutricionista')->first()->id ?? $positions->random()->id,
                'hire_date' => '2020-01-15',
                'salary' => 4800.00,
                'address' => $generateAddress(),
                'notes' => 'Nutricionista responsável pelo cardápio escolar.',
                'is_active' => true,
            ],

            // Colaboradores Inativos (para teste)
            [
                'name' => 'João Inativo',
                'email' => 'joao.inativo@escola.com',
                'phone' => $generatePhone(),
                'cpf' => $generateCpf(),
                'rg' => $generateRg(),
                'birth_date' => '1985-03-15',
                'position_id' => $positions->random()->id,
                'hire_date' => '2018-01-10',
                'salary' => 3000.00,
                'address' => $generateAddress(),
                'notes' => 'Colaborador inativo para teste de filtros.',
                'is_active' => false,
            ],
        ];

        foreach ($employees as $employee) {
            // Verificar se já existe pelo email ou CPF
            $existing = Employee::where('email', $employee['email'])
                ->orWhere('cpf', $employee['cpf'])
                ->first();
            
            if (!$existing) {
                Employee::create($employee);
            }
        }

        // Criar colaboradores adicionais aleatórios
        $firstNames = ['Ana', 'Maria', 'João', 'Pedro', 'Carlos', 'Fernanda', 'Patrícia', 'Roberto', 'Juliana', 'Marcos', 'Lucia', 'Rosa', 'José', 'Antônio', 'Francisco', 'Paulo', 'Camila', 'Gabriel', 'Lucas', 'Isabela'];
        $lastNames = ['Silva', 'Santos', 'Oliveira', 'Souza', 'Pereira', 'Costa', 'Rodrigues', 'Alves', 'Lima', 'Ferreira', 'Martins', 'Carvalho', 'Araújo', 'Barbosa', 'Ribeiro', 'Gomes', 'Dias', 'Moreira', 'Cavalcanti', 'Rocha'];

        for ($i = 0; $i < 10; $i++) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $fullName = $firstName . ' ' . $lastName;
            $email = Str::slug($firstName . '.' . $lastName) . '@escola.com';

            // Evitar duplicatas
            if (Employee::where('email', $email)->exists()) {
                $email = Str::slug($firstName . '.' . $lastName) . rand(1, 999) . '@escola.com';
            }

            Employee::create([
                'name' => $fullName,
                'email' => $email,
                'phone' => $generatePhone(),
                'cpf' => $generateCpf(),
                'rg' => $generateRg(),
                'birth_date' => date('Y-m-d', strtotime('-' . rand(25, 50) . ' years')),
                'position_id' => $positions->random()->id,
                'hire_date' => date('Y-m-d', strtotime('-' . rand(1, 5) . ' years')),
                'salary' => rand(2000, 5000) + (rand(0, 99) / 100),
                'address' => $generateAddress(),
                'notes' => rand(0, 1) ? 'Colaborador dedicado e comprometido com a instituição.' : null,
                'is_active' => rand(0, 10) > 1, // 90% ativos
            ]);
        }

        $this->command->info('Colaboradores criados com sucesso!');
    }
}

