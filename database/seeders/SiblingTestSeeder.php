<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SiblingGroup;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Classroom;
use Carbon\Carbon;

class SiblingTestSeeder extends Seeder
{
    /**
     * Seed completo para cenários de matrículas e irmãos.
     * 
     * CENÁRIOS CRIADOS:
     * 1. Família Silva - 2 irmãos ativos 2024 (teste desconto)
     * 2. Família Santos - Aluno com histórico completo 2022-2024
     * 3. Família Oliveira - 3 irmãos com status diferentes
     * 4. Família Costa - Renovação de matrícula (2023→2024)
     * 5. Alunos individuais - Diferentes status
     * 6. Matrículas pendentes - Aguardando vínculo com turma
     * 7. Matrículas suspensas - Temporariamente inativas
     */
    public function run(): void
    {
        $this->command->info('🌱 Criando cenários de matrículas e irmãos...');

        // CENÁRIO 1: Família Silva - 2 irmãos ativos 2024
        $this->createSilvaFamily();

        // CENÁRIO 2: Família Santos - Histórico completo
        $this->createSantosFamily();

        // CENÁRIO 3: Família Oliveira - 3 irmãos, status diferentes
        $this->createOliveiraFamily();

        // CENÁRIO 4: Família Costa - Renovação de matrícula
        $this->createCostaFamily();

        // CENÁRIO 5: Alunos individuais
        $this->createIndividualStudents();

        // CENÁRIO 6: Matrículas pendentes
        $this->createPendingEnrollments();

        // CENÁRIO 7: Matrículas suspensas
        $this->createSuspendedEnrollments();

        $this->showSummary();
    }

    /**
     * CENÁRIO 1: Família Silva - 2 irmãos ativos 2024
     */
    private function createSilvaFamily(): void
    {
        $this->command->info('  👨‍👩‍👧‍👦 Família Silva (2 irmãos ativos)...');

        // Responsáveis
        $pai = Guardian::factory()->create([
            'name' => 'João Silva',
            'relationship' => 'pai',
            'guardian_type' => 'titular',
            'cpf' => '11111111111',
        ]);

        $mae = Guardian::factory()->create([
            'name' => 'Maria Silva',
            'relationship' => 'mae',
            'guardian_type' => 'financeiro',
            'cpf' => '22222222222',
        ]);

        // Irmão 1 - Pedro
        $pedro = Student::factory()->create([
            'name' => 'Pedro Silva',
            'birth_date' => Carbon::now()->subYears(8)->format('Y-m-d'),
        ]);
        $pedro->guardians()->attach([$pai->id, $mae->id]);

        Enrollment::create([
            'student_id' => $pedro->id,
            'guardian_id' => $pai->id,
            'classroom_id' => null,
            'academic_year' => '2024',
            'status' => 'active',
            'enrollment_date' => Carbon::now()->subMonths(2)->format('Y-m-d'),
            'notes' => 'Irmão mais velho - Matrícula ativa 2024',
        ]);

        // Irmã 2 - Ana
        $ana = Student::factory()->create([
            'name' => 'Ana Silva',
            'birth_date' => Carbon::now()->subYears(6)->format('Y-m-d'),
        ]);
        $ana->guardians()->attach([$pai->id, $mae->id]);

        Enrollment::create([
            'student_id' => $ana->id,
            'guardian_id' => $pai->id,
            'classroom_id' => null,
            'academic_year' => '2024',
            'status' => 'active',
            'enrollment_date' => Carbon::now()->subMonths(2)->format('Y-m-d'),
            'notes' => 'Irmã de Pedro - Deve receber desconto de irmão',
        ]);

        // Grupo de irmãos
        $siblingGroup = SiblingGroup::create([
            'name' => 'Irmãos Silva',
            'description' => 'Família Silva - 2 irmãos',
            'is_active' => true,
        ]);
        
        // Associar guardians ao grupo de irmãos
        $pai->update(['sibling_group_id' => $siblingGroup->id]);
        $mae->update(['sibling_group_id' => $siblingGroup->id]);

        $this->command->info('    ✓ Pedro Silva (8 anos) - Ativo 2024');
        $this->command->info('    ✓ Ana Silva (6 anos) - Ativa 2024 (irmã)');
    }

    /**
     * CENÁRIO 2: Família Santos - Histórico completo 2022-2024
     */
    private function createSantosFamily(): void
    {
        $this->command->info('  👨‍👩‍👦 Família Santos (histórico completo)...');

        $responsavel = Guardian::factory()->create([
            'name' => 'Carla Santos',
            'relationship' => 'mae',
            'guardian_type' => 'titular',
        ]);

        $carlos = Student::factory()->create([
            'name' => 'Carlos Santos',
            'birth_date' => Carbon::now()->subYears(10)->format('Y-m-d'),
        ]);
        $carlos->guardians()->attach($responsavel->id);

        // Matrícula 2022 - Completada
        Enrollment::create([
            'student_id' => $carlos->id,
            'guardian_id' => $responsavel->id,
            'classroom_id' => null,
            'academic_year' => '2022',
            'status' => 'completed',
            'enrollment_date' => '2022-01-15',
            'notes' => 'Cursou 2º Ano em 2022',
        ]);

        // Matrícula 2023 - Completada
        Enrollment::create([
            'student_id' => $carlos->id,
            'guardian_id' => $responsavel->id,
            'classroom_id' => null,
            'academic_year' => '2023',
            'status' => 'completed',
            'enrollment_date' => '2023-01-10',
            'notes' => 'Cursou 3º Ano em 2023',
        ]);

        // Matrícula 2024 - Ativa
        Enrollment::create([
            'student_id' => $carlos->id,
            'guardian_id' => $responsavel->id,
            'classroom_id' => null,
            'academic_year' => '2024',
            'status' => 'active',
            'enrollment_date' => '2024-01-08',
            'notes' => 'Cursando 4º Ano em 2024',
        ]);

        $this->command->info('    ✓ Carlos Santos - 2022(✓), 2023(✓), 2024(ativo)');
    }

    /**
     * CENÁRIO 3: Família Oliveira - 3 irmãos, status diferentes
     */
    private function createOliveiraFamily(): void
    {
        $this->command->info('  👨‍👩‍👧‍👦 Família Oliveira (3 irmãos, status mistos)...');

        $pai = Guardian::factory()->create([
            'name' => 'Roberto Oliveira',
            'relationship' => 'pai',
            'guardian_type' => 'titular',
        ]);

        // Irmão 1 - Ativo
        $lucas = Student::factory()->create([
            'name' => 'Lucas Oliveira',
            'birth_date' => Carbon::now()->subYears(9)->format('Y-m-d'),
        ]);
        $lucas->guardians()->attach($pai->id);

        Enrollment::create([
            'student_id' => $lucas->id,
            'guardian_id' => $pai->id,
            'classroom_id' => null,
            'academic_year' => '2024',
            'status' => 'active',
            'enrollment_date' => '2024-01-20',
            'notes' => 'Irmão mais velho - Ativo',
        ]);

        // Irmã 2 - Ativa
        $julia = Student::factory()->create([
            'name' => 'Julia Oliveira',
            'birth_date' => Carbon::now()->subYears(7)->format('Y-m-d'),
        ]);
        $julia->guardians()->attach($pai->id);

        Enrollment::create([
            'student_id' => $julia->id,
            'guardian_id' => $pai->id,
            'classroom_id' => null,
            'academic_year' => '2024',
            'status' => 'active',
            'enrollment_date' => '2024-01-20',
            'notes' => 'Irmã do meio - Ativa',
        ]);

        // Irmão 3 - Cancelado
        $gabriel = Student::factory()->create([
            'name' => 'Gabriel Oliveira',
            'birth_date' => Carbon::now()->subYears(5)->format('Y-m-d'),
        ]);
        $gabriel->guardians()->attach($pai->id);

        Enrollment::create([
            'student_id' => $gabriel->id,
            'guardian_id' => $pai->id,
            'classroom_id' => null,
            'academic_year' => '2024',
            'status' => 'cancelled',
            'enrollment_date' => '2024-01-20',
            'notes' => 'Matrícula cancelada - Transferiu de escola',
        ]);

        // Grupo de irmãos
        $siblingGroup = SiblingGroup::create([
            'name' => 'Irmãos Oliveira',
            'description' => 'Família Oliveira - 3 irmãos',
            'is_active' => true,
        ]);
        
        // Associar guardian ao grupo de irmãos
        $pai->update(['sibling_group_id' => $siblingGroup->id]);

        $this->command->info('    ✓ Lucas (9) - Ativo | Julia (7) - Ativa | Gabriel (5) - Cancelado');
    }

    /**
     * CENÁRIO 4: Família Costa - Renovação de matrícula
     */
    private function createCostaFamily(): void
    {
        $this->command->info('  👨‍👩‍👦 Família Costa (renovação 2023→2024)...');

        $responsavel = Guardian::factory()->create([
            'name' => 'Sandra Costa',
            'relationship' => 'mae',
        ]);

        $aluno = Student::factory()->create([
            'name' => 'Felipe Costa',
            'birth_date' => Carbon::now()->subYears(8)->format('Y-m-d'),
        ]);
        $aluno->guardians()->attach($responsavel->id);

        // Matrícula 2023 - Completada
        Enrollment::create([
            'student_id' => $aluno->id,
            'guardian_id' => $responsavel->id,
            'classroom_id' => null,
            'academic_year' => '2023',
            'status' => 'completed',
            'enrollment_date' => '2023-01-15',
            'notes' => 'Cursou 1º Ano em 2023',
        ]);

        // Matrícula 2024 - Renovação
        Enrollment::create([
            'student_id' => $aluno->id,
            'guardian_id' => $responsavel->id,
            'classroom_id' => null,
            'academic_year' => '2024',
            'status' => 'active',
            'enrollment_date' => '2024-01-05',
            'notes' => 'Renovação - Cursando 2º Ano',
        ]);

        $this->command->info('    ✓ Felipe Costa - 2023(concluído) → 2024(ativo)');
    }

    /**
     * CENÁRIO 5: Alunos individuais
     */
    private function createIndividualStudents(): void
    {
        $this->command->info('  👤 Alunos individuais (sem irmãos)...');

        // Aluno 1 - Transferido de outra escola
        $resp1 = Guardian::factory()->create(['name' => 'Paula Ferreira']);
        $aluno1 = Student::factory()->create([
            'name' => 'Rafael Ferreira',
            'birth_date' => Carbon::now()->subYears(7)->format('Y-m-d'),
        ]);
        $aluno1->guardians()->attach($resp1->id);

        Enrollment::create([
            'student_id' => $aluno1->id,
            'guardian_id' => $resp1->id,
            'classroom_id' => null,
            'academic_year' => '2024',
            'status' => 'active',
            'enrollment_date' => '2024-02-01',
            'notes' => 'Transferido de outra escola',
        ]);

        // Aluno 2 - Primeira matrícula
        $resp2 = Guardian::factory()->create(['name' => 'Marcos Lima']);
        $aluno2 = Student::factory()->create([
            'name' => 'Beatriz Lima',
            'birth_date' => Carbon::now()->subYears(6)->format('Y-m-d'),
        ]);
        $aluno2->guardians()->attach($resp2->id);

        Enrollment::create([
            'student_id' => $aluno2->id,
            'guardian_id' => $resp2->id,
            'classroom_id' => null,
            'academic_year' => '2024',
            'status' => 'active',
            'enrollment_date' => '2024-03-10',
            'notes' => 'Primeira matrícula na escola',
        ]);

        $this->command->info('    ✓ Rafael Ferreira (transferido) | Beatriz Lima (nova)');
    }

    /**
     * CENÁRIO 6: Matrículas pendentes
     */
    private function createPendingEnrollments(): void
    {
        $this->command->info('  ⏳ Matrículas pendentes (sem turma)...');

        for ($i = 1; $i <= 3; $i++) {
            $resp = Guardian::factory()->create();
            $aluno = Student::factory()->create([
                'name' => "Aluno Pendente {$i}",
                'birth_date' => Carbon::now()->subYears(6 + $i)->format('Y-m-d'),
            ]);
            $aluno->guardians()->attach($resp->id);

            Enrollment::create([
                'student_id' => $aluno->id,
                'guardian_id' => $resp->id,
                'classroom_id' => null,
                'academic_year' => '2024',
                'status' => 'pending',
                'enrollment_date' => Carbon::now()->format('Y-m-d'),
                'notes' => 'Aguardando definição de turma e documentação',
            ]);
        }

        $this->command->info('    ✓ 3 matrículas pendentes criadas');
    }

    /**
     * CENÁRIO 7: Matrículas suspensas
     */
    private function createSuspendedEnrollments(): void
    {
        $this->command->info('  ⏸️  Matrículas suspensas...');

        // Suspenso por saúde
        $resp1 = Guardian::factory()->create(['name' => 'Laura Mendes']);
        $aluno1 = Student::factory()->create([
            'name' => 'Thiago Mendes',
            'birth_date' => Carbon::now()->subYears(8)->format('Y-m-d'),
        ]);
        $aluno1->guardians()->attach($resp1->id);

        Enrollment::create([
            'student_id' => $aluno1->id,
            'guardian_id' => $resp1->id,
            'classroom_id' => null,
            'academic_year' => '2024',
            'status' => 'suspended',
            'enrollment_date' => '2024-01-15',
            'notes' => 'Suspenso - Tratamento de saúde',
        ]);

        // Suspenso por viagem
        $resp2 = Guardian::factory()->create(['name' => 'Ricardo Alves']);
        $aluno2 = Student::factory()->create([
            'name' => 'Isabella Alves',
            'birth_date' => Carbon::now()->subYears(9)->format('Y-m-d'),
        ]);
        $aluno2->guardians()->attach($resp2->id);

        Enrollment::create([
            'student_id' => $aluno2->id,
            'guardian_id' => $resp2->id,
            'classroom_id' => null,
            'academic_year' => '2024',
            'status' => 'suspended',
            'enrollment_date' => '2024-01-20',
            'notes' => 'Suspenso - Viagem prolongada',
        ]);

        $this->command->info('    ✓ 2 matrículas suspensas (saúde, viagem)');
    }

    /**
     * Exibe resumo dos dados criados
     */
    private function showSummary(): void
    {
        $this->command->newLine();
        $this->command->info('📊 RESUMO:');
        $this->command->info('  • Alunos criados: ' . Student::count());
        $this->command->info('  • Responsáveis: ' . Guardian::count());
        $this->command->info('  • Matrículas: ' . Enrollment::count());
        $this->command->info('  • Grupos de irmãos: ' . SiblingGroup::count());
        $this->command->newLine();
        $this->command->info('📋 MATRÍCULAS POR STATUS:');
        $this->command->info('  • Ativas: ' . Enrollment::where('status', 'active')->count());
        $this->command->info('  • Pendentes: ' . Enrollment::where('status', 'pending')->count());
        $this->command->info('  • Suspensas: ' . Enrollment::where('status', 'suspended')->count());
        $this->command->info('  • Canceladas: ' . Enrollment::where('status', 'cancelled')->count());
        $this->command->info('  • Completadas: ' . Enrollment::where('status', 'completed')->count());
        $this->command->newLine();
        $this->command->warn('💡 Próximo passo: Vincule os alunos às turmas via interface!');
    }
}
