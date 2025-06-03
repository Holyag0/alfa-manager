<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Guardian;
use Faker\Factory as Faker;

class GuardianSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('pt_BR');

        // Lista de profissões comuns
        $professions = [
            'Advogado(a)', 'Médico(a)', 'Enfermeiro(a)', 'Professor(a)', 'Engenheiro(a)',
            'Contador(a)', 'Vendedor(a)', 'Empresário(a)', 'Funcionário Público',
            'Técnico(a)', 'Motorista', 'Cozinheiro(a)', 'Comerciante', 'Autônomo(a)',
            'Farmacêutico(a)', 'Dentista', 'Fisioterapeuta', 'Psicólogo(a)', 'Designer',
            'Programador(a)', 'Arquiteto(a)', 'Jornalista', 'Do lar', 'Aposentado(a)'
        ];

        // Criar 50 responsáveis
        for ($i = 0; $i < 50; $i++) {
            $gender = $faker->randomElement(['M', 'F']);
            $firstName = $gender === 'M' ? $faker->firstNameMale : $faker->firstNameFemale;
            
            Guardian::create([
                'name' => $firstName . ' ' . $faker->lastName,
                'cpf' => $faker->unique()->cpf(false), // Sem pontuação
                'rg' => $faker->rg(false),
                'birth_date' => $faker->dateTimeBetween('-55 years', '-25 years')->format('Y-m-d'),
                'gender' => $gender,
                'phone' => $faker->cellphone(false), // Sem formatação
                'email' => $faker->unique()->email,
                'profession' => $faker->randomElement($professions),
                'address' => [
                    'street' => $faker->streetName,
                    'number' => $faker->buildingNumber,
                    'complement' => rand(1, 4) === 1 ? 'Apto ' . rand(1, 100) : null,
                    'district' => $faker->randomElement([
                        'Centro', 'Aldeota', 'Meireles', 'Cocó', 'Papicu', 'Varjota',
                        'Dionísio Torres', 'Benfica', 'José Bonifácio', 'Montese'
                    ]),
                    'city' => $faker->city,
                    'state' => $faker->stateAbbr,
                    'zipcode' => $faker->postcode,
                ],
            ]);
        }

        // Criar alguns responsáveis específicos para facilitar os testes
        $specificGuardians = [
            [
                'name' => 'Maria Silva Santos',
                'cpf' => '12345678901',
                'phone' => '85987654321',
                'email' => 'maria.silva@email.com',
                'profession' => 'Professora',
                'gender' => 'F',
            ],
            [
                'name' => 'João Carlos Oliveira',
                'cpf' => '98765432109',
                'phone' => '85912345678',
                'email' => 'joao.carlos@email.com',
                'profession' => 'Engenheiro',
                'gender' => 'M',
            ],
            [
                'name' => 'Ana Paula Costa',
                'cpf' => '11122233344',
                'phone' => '85999887766',
                'email' => 'ana.paula@email.com',
                'profession' => 'Médica',
                'gender' => 'F',
            ],
        ];

        foreach ($specificGuardians as $guardianData) {
            Guardian::create(array_merge($guardianData, [
                'rg' => $faker->rg(false),
                'birth_date' => $faker->dateTimeBetween('-45 years', '-30 years')->format('Y-m-d'),
                'address' => [
                    'street' => $faker->streetName,
                    'number' => $faker->buildingNumber,
                    'district' => $faker->randomElement([
                        'Centro', 'Aldeota', 'Meireles', 'Cocó', 'Papicu', 'Varjota',
                        'Dionísio Torres', 'Benfica', 'José Bonifácio', 'Montese'
                    ]),
                    'city' => 'Fortaleza',
                    'state' => 'CE',
                    'zipcode' => $faker->postcode,
                ],
            ]));
        }

        $this->command->info('✅ Responsáveis criados com sucesso!');
    }
}