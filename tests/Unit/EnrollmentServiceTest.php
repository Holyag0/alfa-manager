<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\EnrollmentService;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\Classroom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Exception;

class EnrollmentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $enrollmentService;
    protected $student;
    protected $guardian;
    protected $classroom;

    protected function setUp(): void
    {
        parent::setUp();
        $this->enrollmentService = new EnrollmentService();
        
        // Criar dados de teste
        $this->student = Student::factory()->create();
        $this->guardian = Guardian::factory()->create();
        $this->classroom = Classroom::factory()->create(['vacancies' => 2]);
    }

    /** @test */
    public function it_creates_enrollment_successfully()
    {
        $data = [
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardian->id,
            'classroom_id' => $this->classroom->id,
            'status' => 'active',
            'process' => 'completa',
            'enrollment_date' => now(),
            'notes' => 'Test enrollment'
        ];

        $enrollment = $this->enrollmentService->createEnrollment($data);

        $this->assertInstanceOf(Enrollment::class, $enrollment);
        $this->assertEquals($this->student->id, $enrollment->student_id);
        $this->assertEquals($this->guardian->id, $enrollment->guardian_id);
        $this->assertEquals($this->classroom->id, $enrollment->classroom_id);
    }

    /** @test */
    public function it_prevents_duplicate_enrollment()
    {
        // Criar primeira matrícula
        $data = [
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardian->id,
            'classroom_id' => $this->classroom->id,
        ];

        $this->enrollmentService->createEnrollment($data);

        // Tentar criar segunda matrícula - deve falhar
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Student is already enrolled in this classroom.');
        
        $this->enrollmentService->createEnrollment($data);
    }

    /** @test */
    public function it_prevents_over_enrollment()
    {
        // Criar segundo aluno e responsável
        $student2 = Student::factory()->create();
        $guardian2 = Guardian::factory()->create();

        // Criar matrículas até esgotar vagas
        $data1 = [
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardian->id,
            'classroom_id' => $this->classroom->id,
        ];

        $data2 = [
            'student_id' => $student2->id,
            'guardian_id' => $guardian2->id,
            'classroom_id' => $this->classroom->id,
        ];

        // Primeira matrícula deve funcionar
        $this->enrollmentService->createEnrollment($data1);

        // Segunda matrícula deve funcionar (ainda há 1 vaga)
        $this->enrollmentService->createEnrollment($data2);

        // Terceira matrícula deve falhar (sem vagas)
        $student3 = Student::factory()->create();
        $guardian3 = Guardian::factory()->create();
        $data3 = [
            'student_id' => $student3->id,
            'guardian_id' => $guardian3->id,
            'classroom_id' => $this->classroom->id,
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No vacancies available in this classroom.');
        
        $this->enrollmentService->createEnrollment($data3);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Field 'student_id' is required.");
        
        $this->enrollmentService->createEnrollment([]);
    }

    /** @test */
    public function it_validates_student_exists()
    {
        $data = [
            'student_id' => 99999, // ID inexistente
            'guardian_id' => $this->guardian->id,
            'classroom_id' => $this->classroom->id,
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Student not found.');
        
        $this->enrollmentService->createEnrollment($data);
    }

    /** @test */
    public function it_validates_guardian_exists()
    {
        $data = [
            'student_id' => $this->student->id,
            'guardian_id' => 99999, // ID inexistente
            'classroom_id' => $this->classroom->id,
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Guardian not found.');
        
        $this->enrollmentService->createEnrollment($data);
    }

    /** @test */
    public function it_validates_classroom_exists()
    {
        $data = [
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardian->id,
            'classroom_id' => 99999, // ID inexistente
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Classroom not found.');
        
        $this->enrollmentService->createEnrollment($data);
    }

    /** @test */
    public function it_changes_classroom_successfully()
    {
        // Criar matrícula inicial
        $data = [
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardian->id,
            'classroom_id' => $this->classroom->id,
        ];

        $enrollment = $this->enrollmentService->createEnrollment($data);

        // Criar nova turma
        $newClassroom = Classroom::factory()->create(['vacancies' => 1]);

        // Mudar turma
        $updatedEnrollment = $this->enrollmentService->changeClassroom($enrollment->id, $newClassroom->id);

        $this->assertEquals($newClassroom->id, $updatedEnrollment->classroom_id);
    }

    /** @test */
    public function it_prevents_changing_to_same_classroom()
    {
        // Criar matrícula inicial
        $data = [
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardian->id,
            'classroom_id' => $this->classroom->id,
        ];

        $enrollment = $this->enrollmentService->createEnrollment($data);

        // Tentar mudar para a mesma turma - deve falhar
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Student is already enrolled in this classroom.');
        
        $this->enrollmentService->changeClassroom($enrollment->id, $this->classroom->id);
    }

    /** @test */
    public function it_prevents_changing_to_full_classroom()
    {
        // Criar matrícula inicial
        $data = [
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardian->id,
            'classroom_id' => $this->classroom->id,
        ];

        $enrollment = $this->enrollmentService->createEnrollment($data);

        // Criar nova turma com apenas 1 vaga
        $newClassroom = Classroom::factory()->create(['vacancies' => 1]);
        
        // Ocupar a vaga da nova turma
        $student2 = Student::factory()->create();
        $guardian2 = Guardian::factory()->create();
        $data2 = [
            'student_id' => $student2->id,
            'guardian_id' => $guardian2->id,
            'classroom_id' => $newClassroom->id,
        ];
        $this->enrollmentService->createEnrollment($data2);

        // Tentar mudar para turma cheia - deve falhar
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No vacancies available in the new classroom.');
        
        $this->enrollmentService->changeClassroom($enrollment->id, $newClassroom->id);
    }
}
