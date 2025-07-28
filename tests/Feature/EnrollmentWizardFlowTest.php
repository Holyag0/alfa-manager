<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Classroom;
use Spatie\Permission\Models\Role;

class EnrollmentWizardFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create user with permissions and authorization
        $this->user = User::factory()->create([
            'is_auth' => 1, // Usuário autorizado a acessar o sistema
        ]);
        $role = Role::create(['name' => 'admin']);
        $this->user->assignRole($role);
    }

    /** @test */
    public function user_can_complete_full_enrollment_wizard_flow()
    {
        // Arrange
        $classroom = Classroom::factory()->create(['name' => 'Turma A', 'vacancies' => 30]);

        // Act & Assert - STEP 1: Start Wizard
        $response = $this->actingAs($this->user)
            ->get(route('matriculas.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Matriculas/Wizard')
            ->has('classrooms')
        );

        // Act & Assert - STEP 2: Submit Student Data (AlunoStep)
        $studentData = [
            'step' => 'aluno',
            'student' => [
                'name' => 'João Silva Santos',
                'birth_date' => '2010-05-15',
                'cpf' => '12345678901',
                'rg' => '123456789',
                'birth_certificate_number' => '987654321',
                'notes' => 'Aluno dedicado aos estudos',
            ]
        ];

        $studentStepResponse = $this->actingAs($this->user)
            ->post(route('matriculas.wizard.store'), $studentData);

        // Should redirect back to wizard with student data in session
        $studentStepResponse->assertRedirect();
        $studentStepResponse->assertSessionHas('enrollment_wizard.student');

        // Verify student data is stored in session
        $sessionData = session('enrollment_wizard.student');
        $this->assertEquals('João Silva Santos', $sessionData['name']);
        $this->assertEquals('12345678901', $sessionData['cpf']);

        // Act & Assert - STEP 3: Submit Guardian Data (ResponsavelStep)
        $guardianData = [
            'step' => 'responsavel',
            'guardian' => [
                'name' => 'Maria Silva Santos',
                'cpf' => '98765432100',
                'email' => 'maria@email.com',
                'phone' => '(11) 99999-9999',
                'guardian_type' => 'titular',
                'relationship' => 'mae',
                'occupation' => 'Professora',
                'workplace' => 'Escola Municipal',
                'addresses' => [
                    [
                        'type' => 'residencial',
                        'zip_code' => '01234-567',
                        'street' => 'Rua das Flores',
                        'number' => '123',
                        'complement' => 'Apto 45',
                        'neighborhood' => 'Centro',
                        'city' => 'São Paulo',
                        'state' => 'SP',
                        'address_for' => 'casa',
                        'is_primary' => true,
                    ]
                ],
                'contacts' => [
                    [
                        'type' => 'email',
                        'value' => 'maria.trabalho@escola.com',
                        'label' => 'trabalho',
                        'is_primary' => false,
                    ]
                ]
            ]
        ];

        $guardianStepResponse = $this->actingAs($this->user)
            ->post(route('matriculas.wizard.store'), $guardianData);

        // Should redirect back to wizard with guardian data in session
        $guardianStepResponse->assertRedirect();
        $guardianStepResponse->assertSessionHas('enrollment_wizard.guardian');

        // Verify guardian data is stored in session
        $guardianSessionData = session('enrollment_wizard.guardian');
        $this->assertEquals('Maria Silva Santos', $guardianSessionData['name']);
        $this->assertEquals('98765432100', $guardianSessionData['cpf']);
        $this->assertEquals('titular', $guardianSessionData['guardian_type']);

        // Act & Assert - STEP 4: Submit Enrollment Data (MatriculaStep)
        $enrollmentData = [
            'step' => 'matricula',
            'enrollment' => [
                'classroom_id' => $classroom->id,
                'enrollment_date' => now()->format('Y-m-d'),
                'status' => 'active',
                'notes' => 'Matrícula realizada via wizard',
            ]
        ];

        $enrollmentStepResponse = $this->actingAs($this->user)
            ->post(route('matriculas.wizard.store'), $enrollmentData);

        // Should redirect back to wizard with enrollment data in session
        $enrollmentStepResponse->assertRedirect();
        $enrollmentStepResponse->assertSessionHas('enrollment_wizard.enrollment');

        // Verify enrollment data is stored in session
        $enrollmentSessionData = session('enrollment_wizard.enrollment');
        $this->assertEquals($classroom->id, $enrollmentSessionData['classroom_id']);
        $this->assertEquals('active', $enrollmentSessionData['status']);

        // Act & Assert - STEP 5: Final Submission (Complete Wizard)
        $finalSubmissionResponse = $this->actingAs($this->user)
            ->post(route('matriculas.wizard.complete'));

        // Should redirect to enrollments index with success message
        $finalSubmissionResponse->assertRedirect(route('matriculas.index'));
        $finalSubmissionResponse->assertSessionHas('success');

        // Verify all data was created in database
        $this->assertDatabaseHas('students', [
            'name' => 'João Silva Santos',
            'cpf' => '12345678901',
            'birth_certificate_number' => '987654321',
        ]);

        $this->assertDatabaseHas('guardians', [
            'name' => 'Maria Silva Santos',
            'cpf' => '98765432100',
            'guardian_type' => 'titular',
            'relationship' => 'mae',
        ]);

        $this->assertDatabaseHas('enrollments', [
            'classroom_id' => $classroom->id,
            'status' => 'active',
            'notes' => 'Matrícula realizada via wizard',
        ]);

        // Verify relationships were created
        $this->assertDatabaseHas('guardian_student', []);

        // Verify contacts were created
        $this->assertDatabaseHas('contacts', [
            'type' => 'email',
            'value' => 'maria@email.com',
            'is_primary' => true,
        ]);

        // Verify addresses were created
        $this->assertDatabaseHas('addresses', [
            'type' => 'residencial',
            'street' => 'Rua das Flores',
            'number' => '123',
            'city' => 'São Paulo',
            'is_primary' => true,
        ]);

        // Verify session was cleared after completion
        $this->assertNull(session('enrollment_wizard'));
    }

    /** @test */
    public function wizard_validates_student_data_in_first_step()
    {
        // Act - Submit invalid student data
        $invalidStudentData = [
            'step' => 'aluno',
            'student' => [
                'name' => '', // Required field empty
                'birth_date' => 'invalid-date',
                'cpf' => '123', // Invalid CPF
            ]
        ];

        $response = $this->actingAs($this->user)
            ->post(route('matriculas.wizard.store'), $invalidStudentData);

        // Assert - Should return with validation errors
        $response->assertSessionHasErrors(['student.name', 'student.birth_date', 'student.cpf']);
        $this->assertNull(session('enrollment_wizard.student'));
    }

    /** @test */
    public function wizard_validates_guardian_data_in_second_step()
    {
        // Arrange - Set student data in session first
        session(['enrollment_wizard.student' => [
            'name' => 'João Silva',
            'cpf' => '12345678901',
        ]]);

        // Act - Submit invalid guardian data
        $invalidGuardianData = [
            'step' => 'responsavel',
            'guardian' => [
                'name' => '', // Required field empty
                'cpf' => '123', // Invalid CPF
                'email' => 'invalid-email', // Invalid email
                'guardian_type' => 'invalid_type', // Invalid type
            ]
        ];

        $response = $this->actingAs($this->user)
            ->post(route('matriculas.wizard.store'), $invalidGuardianData);

        // Assert - Should return with validation errors
        $response->assertSessionHasErrors(['guardian.name', 'guardian.cpf', 'guardian.email', 'guardian.guardian_type']);
        $this->assertNull(session('enrollment_wizard.guardian'));
    }

    /** @test */
    public function wizard_prevents_completing_without_all_steps()
    {
        // Act - Try to complete wizard without any data
        $response = $this->actingAs($this->user)
            ->post(route('matriculas.wizard.complete'));

        // Assert - Should redirect back with error
        $response->assertRedirect();
        $response->assertSessionHasErrors(['wizard' => 'Por favor, complete todos os passos do wizard.']);
    }

    /** @test */
    public function wizard_maintains_state_when_user_goes_back_and_forth()
    {
        // Arrange - Set initial student data
        $studentData = [
            'step' => 'aluno',
            'student' => [
                'name' => 'João Silva',
                'cpf' => '12345678901',
                'birth_date' => '2010-05-15',
            ]
        ];

        // Act - Submit student step
        $this->actingAs($this->user)
            ->post(route('matriculas.wizard.store'), $studentData);

        // Act - Go back to wizard and check if data persists
        $response = $this->actingAs($this->user)
            ->get(route('matriculas.create'));

        // Assert - Student data should be pre-filled
        $response->assertInertia(fn ($page) => $page
            ->component('Matriculas/Wizard')
            ->where('wizardData.student.name', 'João Silva')
            ->where('wizardData.student.cpf', '12345678901')
        );
    }

    /** @test */
    public function wizard_handles_classroom_capacity_validation()
    {
        // Arrange - Create classroom with only 1 vacancy
        $classroom = Classroom::factory()->create(['vacancies' => 1]);
        
        // Fill the classroom
        \App\Models\Enrollment::factory()->create([
            'classroom_id' => $classroom->id,
            'status' => 'active',
        ]);

        // Set up wizard session with student and guardian data
        session([
            'enrollment_wizard.student' => ['name' => 'João Silva', 'cpf' => '12345678901'],
            'enrollment_wizard.guardian' => ['name' => 'Maria Silva', 'cpf' => '98765432100'],
        ]);

        // Act - Try to enroll in full classroom
        $enrollmentData = [
            'step' => 'matricula',
            'enrollment' => [
                'classroom_id' => $classroom->id,
                'enrollment_date' => now()->format('Y-m-d'),
                'status' => 'active',
            ]
        ];

        $response = $this->actingAs($this->user)
            ->post(route('matriculas.wizard.store'), $enrollmentData);

        // Assert - Should return with capacity error
        $response->assertSessionHasErrors(['enrollment.classroom_id']);
        $this->assertNull(session('enrollment_wizard.enrollment'));
    }

    /** @test */
    public function user_can_reset_wizard_and_start_over()
    {
        // Arrange - Set some wizard data
        session([
            'enrollment_wizard.student' => ['name' => 'João Silva'],
            'enrollment_wizard.guardian' => ['name' => 'Maria Silva'],
        ]);

        // Act - Reset wizard
        $response = $this->actingAs($this->user)
            ->post(route('matriculas.wizard.reset'));

        // Assert - Session should be cleared and redirect to start
        $response->assertRedirect(route('matriculas.create'));
        $this->assertNull(session('enrollment_wizard'));
    }
} 