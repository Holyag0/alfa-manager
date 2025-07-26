<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\Classroom;
use App\Models\Enrollment;
use Spatie\Permission\Models\Role;

class EnrollmentFlowTest extends TestCase
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
    public function user_can_view_enrollment_index_page()
    {
        // Arrange
        Enrollment::factory()->count(3)->create();

        // Act
        $response = $this->actingAs($this->user)->get(route('matriculas.index'));

        // Assert
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Matriculas/Index')
            ->has('enrollments')
        );
    }

    /** @test */
    public function user_can_view_enrollment_create_page()
    {
        // Act
        $response = $this->actingAs($this->user)->get(route('matriculas.create'));

        // Assert
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Matriculas/Wizard')
        );
    }

    /** @test */
    public function user_can_create_complete_enrollment_flow()
    {
        // Arrange
        $classroom = Classroom::factory()->create(['vacancies' => 30]);
        
        $studentData = [
            'name' => 'João Silva Santos',
            'birth_date' => '2010-05-15',
            'cpf' => '12345678901',
            'rg' => '123456789',
            'notes' => 'Aluno aplicado',
        ];

        $guardianData = [
            'name' => 'Maria Silva Santos',
            'cpf' => '98765432100',
            'email' => 'maria@email.com',
            'phone' => '(11) 99999-9999',
            'guardian_type' => 'titular',
            'relationship' => 'mae',
            'occupation' => 'Professora',
            'addresses' => [
                [
                    'type' => 'residencial',
                    'zip_code' => '01234-567',
                    'street' => 'Rua das Flores',
                    'number' => '123',
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
        ];

        // Act - Create Student
        $studentResponse = $this->actingAs($this->user)
            ->post(route('students.store'), $studentData);

        // Assert Student Creation
        $studentResponse->assertRedirect();
        $this->assertDatabaseHas('students', [
            'name' => 'João Silva Santos',
            'cpf' => '12345678901',
        ]);

        $student = Student::where('cpf', '12345678901')->first();
        $this->assertNotNull($student);

        // Act - Create Guardian
        $guardianResponse = $this->actingAs($this->user)
            ->post(route('guardian.store'), $guardianData);

        // Assert Guardian Creation
        $guardianResponse->assertRedirect();
        $this->assertDatabaseHas('guardians', [
            'name' => 'Maria Silva Santos',
            'cpf' => '98765432100',
            'guardian_type' => 'titular',
        ]);

        $guardian = Guardian::where('cpf', '98765432100')->first();
        $this->assertNotNull($guardian);

        // Check contacts were created
        $this->assertDatabaseHas('contacts', [
            'guardian_id' => $guardian->id,
            'type' => 'email',
            'value' => 'maria@email.com',
            'is_primary' => true,
        ]);

        // Act - Link Guardian to Student
        $linkResponse = $this->actingAs($this->user)
            ->post(route('students.guardians.attach', $student->id), [
                'guardian_id' => $guardian->id,
            ]);

        // Assert Link
        $linkResponse->assertRedirect();
        $this->assertDatabaseHas('guardian_student', [
            'guardian_id' => $guardian->id,
            'student_id' => $student->id,
        ]);

        // Act - Create Enrollment
        $enrollmentData = [
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'classroom_id' => $classroom->id,
            'enrollment_date' => now()->format('Y-m-d'),
            'status' => 'active',
            'notes' => 'Matrícula realizada com sucesso',
        ];

        $enrollmentResponse = $this->actingAs($this->user)
            ->post(route('matriculas.store'), $enrollmentData);

        // Assert Enrollment Creation
        $enrollmentResponse->assertRedirect(route('matriculas.index'));
        $this->assertDatabaseHas('enrollments', [
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'classroom_id' => $classroom->id,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function user_can_view_enrollment_details()
    {
        // Arrange
        $enrollment = Enrollment::factory()->create();

        // Act
        $response = $this->actingAs($this->user)
            ->get(route('matriculas.edit', $enrollment->id));

        // Assert
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Matriculas/Edit')
            ->has('enrollment')
        );
    }

    /** @test */
    public function user_can_edit_enrollment()
    {
        // Arrange
        $enrollment = Enrollment::factory()->create(['status' => 'pending']);
        $newClassroom = Classroom::factory()->create();

        // Act
        $response = $this->actingAs($this->user)
            ->get(route('matriculas.edit', $enrollment->id));

        // Assert - Can view edit page
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Matriculas/Edit')
            ->has('enrollment')
            ->has('classrooms')
        );

        // Act - Update enrollment
        $updateData = [
            'classroom_id' => $newClassroom->id,
            'status' => 'active',
            'notes' => 'Matrícula atualizada',
        ];

        $updateResponse = $this->actingAs($this->user)
            ->patch(route('matriculas.update', $enrollment->id), $updateData);

        // Assert Update
        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'classroom_id' => $newClassroom->id,
            'status' => 'active',
            'notes' => 'Matrícula atualizada',
        ]);
    }

    /** @test */
    public function user_can_delete_enrollment()
    {
        // Arrange
        $enrollment = Enrollment::factory()->create();

        // Act
        $response = $this->actingAs($this->user)
            ->delete(route('matriculas.destroy', $enrollment->id));

        // Assert
        $response->assertRedirect(route('matriculas.index'));
        $this->assertDatabaseMissing('enrollments', [
            'id' => $enrollment->id,
        ]);
    }

    /** @test */
    public function user_can_search_enrollments()
    {
        // Arrange
        $student1 = Student::factory()->create(['name' => 'João Silva']);
        $student2 = Student::factory()->create(['name' => 'Maria Santos']);
        
        $enrollment1 = Enrollment::factory()->create(['student_id' => $student1->id]);
        $enrollment2 = Enrollment::factory()->create(['student_id' => $student2->id]);

        // Act
        $response = $this->actingAs($this->user)
            ->get(route('matriculas.index', ['student' => 'Silva']));

        // Assert
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Matriculas/Index')
            ->has('enrollments')
            ->where('enrollments.data', fn ($enrollments) => 
                collect($enrollments)->contains('student.name', 'João Silva') &&
                !collect($enrollments)->contains('student.name', 'Maria Santos')
            )
        );
    }

    /** @test */
    public function user_can_manage_student_guardians()
    {
        // Arrange
        $student = Student::factory()->create();
        $guardian1 = Guardian::factory()->create(['name' => 'João Silva']);
        $guardian2 = Guardian::factory()->create(['name' => 'Maria Silva']);
        
        // Link first guardian
        $student->guardians()->attach($guardian1->id);

        // Act - View student edit page (should show guardians)
        $response = $this->actingAs($this->user)
            ->get(route('students.edit', $student->id));

        // Assert
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Alunos/Edit')
            ->has('student')
            ->has('guardians', 1) // Should have 1 guardian
        );

        // Act - Search for guardians not linked to student
        $searchResponse = $this->actingAs($this->user)
            ->get(route('students.guardians.search-not-linked', $student->id), [
                'q' => 'Silva'
            ]);

        // Assert - Should return only unlinked guardian
        $searchResponse->assertOk();
        $searchResponse->assertJsonCount(1); // Only Maria Silva (unlinked)

        // Act - Attach second guardian
        $attachResponse = $this->actingAs($this->user)
            ->post(route('students.guardians.attach', $student->id), [
                'guardian_id' => $guardian2->id,
            ]);

        // Assert
        $attachResponse->assertRedirect();
        $this->assertDatabaseHas('guardian_student', [
            'student_id' => $student->id,
            'guardian_id' => $guardian2->id,
        ]);

        // Act - Detach guardian
        $detachResponse = $this->actingAs($this->user)
            ->delete(route('students.guardians.detach', [$student->id, $guardian1->id]));

        // Assert
        $detachResponse->assertRedirect();
        $this->assertDatabaseMissing('guardian_student', [
            'student_id' => $student->id,
            'guardian_id' => $guardian1->id,
        ]);
    }

    /** @test */
    public function enrollment_prevents_duplicate_student_in_same_classroom()
    {
        // Arrange
        $student = Student::factory()->create();
        $guardian = Guardian::factory()->create();
        $classroom = Classroom::factory()->create(['vacancies' => 30]);

        // Create first enrollment
        Enrollment::factory()->create([
            'student_id' => $student->id,
            'classroom_id' => $classroom->id,
            'status' => 'active',
        ]);

        // Act - Try to create duplicate enrollment
        $response = $this->actingAs($this->user)
            ->post(route('matriculas.store'), [
                'student_id' => $student->id,
                'guardian_id' => $guardian->id,
                'classroom_id' => $classroom->id,
                'enrollment_date' => now()->format('Y-m-d'),
            ]);

        // Assert - Should be rejected
        $response->assertSessionHasErrors();
    }

    /** @test */
    public function enrollment_prevents_exceeding_classroom_capacity()
    {
        // Arrange
        $classroom = Classroom::factory()->create(['vacancies' => 1]);
        
        // Fill the only vacancy
        Enrollment::factory()->create([
            'classroom_id' => $classroom->id,
            'status' => 'active',
        ]);

        $student = Student::factory()->create();
        $guardian = Guardian::factory()->create();

        // Act - Try to enroll in full classroom
        $response = $this->actingAs($this->user)
            ->post(route('matriculas.store'), [
                'student_id' => $student->id,
                'guardian_id' => $guardian->id,
                'classroom_id' => $classroom->id,
                'enrollment_date' => now()->format('Y-m-d'),
            ]);

        // Assert - Should be rejected
        $response->assertSessionHasErrors();
    }
} 