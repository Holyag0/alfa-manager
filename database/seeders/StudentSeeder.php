<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\Classroom;
use App\Models\Enrollment;
use Faker\Factory as Faker;

class StudentSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('pt_BR');
        $guardians = Guardian::all();
        $classrooms = Classroom::where('status', 'active')->get();

        if ($guardians->isEmpty()) {
            $this->command->error('❌ Nenhum responsável encontrado. Execute o GuardianSeeder primeiro.');
            return;
        }

        if ($classrooms->isEmpty()) {
            $this->command->error('❌ Nenhuma turma encontrada. Execute o ClassroomSeeder primeiro.');
            return;
        }

        // Status possíveis para os alunos
        $statuses = ['active', 'active', 'active', 'active', 'inactive']; // 80% ativos

        // Relacionamentos familiares
        $relationships = ['father', 'mother', 'grandfather', 'grandmother', 'uncle', 'aunt', 'other'];

        // Criar 80 alunos
        for ($i = 1; $i <= 80; $i++) {
            $gender = $faker->randomElement(['M', 'F']);
            $firstName = $gender === 'M' ? $faker->firstNameMale : $faker->firstNameFemale;
            $status = $faker->randomElement($statuses);
            
            // Gerar número de matrícula sequencial
            $registrationNumber = now()->year . str_pad($i, 3, '0', STR_PAD_LEFT);

            $student = Student::create([
                'registration_number' => $registrationNumber,
                'name' => $firstName . ' ' . $faker->lastName,
                'birth_date' => $faker->dateTimeBetween('-15 years', '-5 years')->format('Y-m-d'),
                'cpf' => rand(1, 3) === 1 ? $faker->unique()->cpf(false) : null, // Nem todos têm CPF
                'rg' => rand(1, 2) === 1 ? $faker->rg(false) : null,
                'gender' => $gender,
                'phone' => rand(1, 3) === 1 ? $faker->cellphone(false) : null, // Alguns têm telefone próprio
                'email' => rand(1, 4) === 1 ? $faker->unique()->email : null, // Poucos têm email
                'address' => [
                    'street' => $faker->streetName,
                    'number' => $faker->buildingNumber,
                    'complement' => rand(1, 5) === 1 ? 'Apto ' . rand(1, 100) : null,
                    'district' => $faker->randomElement([
                        'Centro', 'Aldeota', 'Meireles', 'Cocó', 'Papicu', 'Varjota',
                        'Dionísio Torres', 'Benfica', 'José Bonifácio', 'Montese',
                        'Barra do Ceará', 'Antônio Bezerra', 'Parangaba', 'Maracanaú'
                    ]),
                    'city' => $faker->city,
                    'state' => $faker->stateAbbr,
                    'zipcode' => $faker->postcode,
                ],
                'status' => $status,
                'enrollment_date' => $faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
                'notes' => rand(1, 5) === 1 ? $faker->sentence : null, // 20% têm observações
            ]);

            // Associar responsáveis (1 a 3 responsáveis por aluno)
            $numGuardians = rand(1, 3);
            $selectedGuardians = $guardians->random($numGuardians);
            
            foreach ($selectedGuardians as $index => $guardian) {
                $isPrimary = $index === 0; // Primeiro responsável é o principal
                $isFinancial = $index === 0 || rand(1, 3) === 1; // Principal sempre é financeiro, outros podem ser
                
                $student->guardians()->attach($guardian->id, [
                    'relationship' => $faker->randomElement($relationships),
                    'is_primary' => $isPrimary,
                    'is_financial' => $isFinancial,
                    'can_pickup' => rand(1, 10) > 1, // 90% podem buscar
                ]);
            }

            // Matricular aluno em turma (apenas se estiver ativo)
            if ($status === 'active') {
                $classroom = $classrooms->where('current_students', '<', 'max_students')->random();
                
                if ($classroom) {
                    Enrollment::create([
                        'student_id' => $student->id,
                        'classroom_id' => $classroom->id,
                        'enrollment_date' => $student->enrollment_date,
                        'status' => 'active',
                    ]);

                    // Atualizar contador da turma
                    $classroom->increment('current_students');
                }
            }
        }

        // Criar alguns alunos específicos para testes
        $specificStudents = [
            [
                'name' => 'Pedro Silva Santos',
                'registration_number' => now()->year . '999',
                'birth_date' => '2015-05-15',
                'gender' => 'M',
                'status' => 'active',
            ],
            [
                'name' => 'Julia Oliveira Costa',
                'registration_number' => now()->year . '998',
                'birth_date' => '2014-08-22',
                'gender' => 'F',
                'status' => 'active',
            ],
        ];

        foreach ($specificStudents as $studentData) {
            $student = Student::create(array_merge($studentData, [
                'cpf' => $faker->unique()->cpf(false),
                'phone' => $faker->cellphone(false),
                'email' => $faker->unique()->email,
                'enrollment_date' => now()->subMonths(6)->format('Y-m-d'),
                'address' => [
                    'street' => $faker->streetName,
                    'number' => $faker->buildingNumber,
                    'district' => 'Centro',
                    'city' => 'Fortaleza',
                    'state' => 'CE',
                    'zipcode' => $faker->postcode,
                ],
            ]));

            // Associar aos responsáveis específicos criados
            $guardian = $guardians->where('email', 'maria.silva@email.com')->first();
            if ($guardian) {
                $student->guardians()->attach($guardian->id, [
                    'relationship' => 'mother',
                    'is_primary' => true,
                    'is_financial' => true,
                    'can_pickup' => true,
                ]);
            }

            // Matricular em turma
            $classroom = $classrooms->where('current_students', '<', 'max_students')->first();
            if ($classroom) {
                Enrollment::create([
                    'student_id' => $student->id,
                    'classroom_id' => $classroom->id,
                    'enrollment_date' => $student->enrollment_date,
                    'status' => 'active',
                ]);
                $classroom->increment('current_students');
            }
        }

        // Atualizar contadores finais das turmas
        foreach ($classrooms as $classroom) {
            $classroom->updateStudentCount();
        }

        $this->command->info('✅ Alunos criados e matriculados com sucesso!');
        $this->command->info('📊 Total de alunos: ' . Student::count());
        $this->command->info('📊 Alunos ativos: ' . Student::where('status', 'active')->count());
        $this->command->info('📊 Total de matrículas ativas: ' . Enrollment::where('status', 'active')->count());
    }
}