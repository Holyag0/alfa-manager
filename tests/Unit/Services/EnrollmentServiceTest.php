<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\EnrollmentService;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\Classroom;
use App\Models\Enrollment;
use Exception;

class EnrollmentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected EnrollmentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EnrollmentService();
    }

    /** @test */
    public function it_creates_enrollment_with_valid_data()
    {
        // Arrange
        $student = Student::factory()->create();
        $guardian = Guardian::factory()->create();
        $classroom = Classroom::factory()->create(['vacancies' => 30]);

        $data = [
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'classroom_id' => $classroom->id,
            'enrollment_date' => now()->format('Y-m-d'),
            'status' => 'active',
            'notes' => 'Test enrollment notes',
        ];

        // Act
        $enrollment = $this->service->createEnrollment($data);

        // Assert
        $this->assertInstanceOf(Enrollment::class, $enrollment);
        $this->assertDatabaseHas('enrollments', [
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'classroom_id' => $classroom->id,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function it_sets_default_status_when_not_provided()
    {
        // Arrange
        $student = Student::factory()->create();
        $guardian = Guardian::factory()->create();
        $classroom = Classroom::factory()->create(['vacancies' => 30]);

        $data = [
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'classroom_id' => $classroom->id,
            'enrollment_date' => now()->format('Y-m-d'),
        ];

        // Act
        $enrollment = $this->service->createEnrollment($data);

        // Assert
        $this->assertEquals('active', $enrollment->status);
    }

    /** @test */
    public function it_prevents_duplicate_enrollment_in_same_classroom()
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

        $data = [
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'classroom_id' => $classroom->id,
        ];

        // Act & Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Student is already enrolled in this classroom.');
        
        $this->service->createEnrollment($data);
    }

    /** @test */
    public function it_prevents_enrollment_when_no_vacancies()
    {
        // Arrange
        $student = Student::factory()->create();
        $guardian = Guardian::factory()->create();
        $classroom = Classroom::factory()->create(['vacancies' => 1]);

        // Fill the only vacancy
        Enrollment::factory()->create([
            'classroom_id' => $classroom->id,
            'status' => 'active',
        ]);

        $data = [
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'classroom_id' => $classroom->id,
        ];

        // Act & Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No vacancies available in this classroom.');
        
        $this->service->createEnrollment($data);
    }

    /** @test */
    public function it_updates_enrollment_successfully()
    {
        // Arrange
        $enrollment = Enrollment::factory()->create([
            'status' => 'pending',
            'notes' => 'Original notes',
        ]);

        $newClassroom = Classroom::factory()->create();
        $updateData = [
            'classroom_id' => $newClassroom->id,
            'status' => 'active',
            'notes' => 'Updated notes',
        ];

        // Act
        $updatedEnrollment = $this->service->updateEnrollment($enrollment->id, $updateData);

        // Assert
        $this->assertEquals($newClassroom->id, $updatedEnrollment->classroom_id);
        $this->assertEquals('active', $updatedEnrollment->status);
        $this->assertEquals('Updated notes', $updatedEnrollment->notes);
    }

    /** @test */
    public function it_cancels_enrollment_successfully()
    {
        // Arrange
        $enrollment = Enrollment::factory()->create(['status' => 'active']);
        $reason = 'Student transferred to another school';

        // Act
        $cancelledEnrollment = $this->service->cancelEnrollment($enrollment->id, $reason);

        // Assert
        $this->assertEquals('cancelled', $cancelledEnrollment->status);
        $this->assertStringContainsString('Cancel reason: ' . $reason, $cancelledEnrollment->notes);
        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'status' => 'cancelled',
        ]);
    }

    /** @test */
    public function it_changes_classroom_successfully()
    {
        // Arrange
        $originalClassroom = Classroom::factory()->create(['vacancies' => 30]);
        $newClassroom = Classroom::factory()->create(['vacancies' => 30]);
        $enrollment = Enrollment::factory()->create([
            'classroom_id' => $originalClassroom->id,
            'status' => 'active',
        ]);

        // Act
        $updatedEnrollment = $this->service->changeClassroom($enrollment->id, $newClassroom->id);

        // Assert
        $this->assertEquals($newClassroom->id, $updatedEnrollment->classroom_id);
        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'classroom_id' => $newClassroom->id,
        ]);
    }

    /** @test */
    public function it_prevents_classroom_change_when_no_vacancies()
    {
        // Arrange
        $originalClassroom = Classroom::factory()->create(['vacancies' => 30]);
        $newClassroom = Classroom::factory()->create(['vacancies' => 1]);
        
        // Fill the only vacancy in new classroom
        Enrollment::factory()->create([
            'classroom_id' => $newClassroom->id,
            'status' => 'active',
        ]);

        $enrollment = Enrollment::factory()->create([
            'classroom_id' => $originalClassroom->id,
            'status' => 'active',
        ]);

        // Act & Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No vacancies available in the new classroom.');
        
        $this->service->changeClassroom($enrollment->id, $newClassroom->id);
    }

    /** @test */
    public function it_searches_enrollments_by_student_name()
    {
        // Arrange
        $student1 = Student::factory()->create(['name' => 'João Silva']);
        $student2 = Student::factory()->create(['name' => 'Maria Santos']);
        $student3 = Student::factory()->create(['name' => 'Pedro Oliveira']);

        Enrollment::factory()->create(['student_id' => $student1->id]);
        Enrollment::factory()->create(['student_id' => $student2->id]);
        Enrollment::factory()->create(['student_id' => $student3->id]);

        // Act
        $results = $this->service->searchEnrollments(['student' => 'Silva']);

        // Assert
        $this->assertEquals(1, $results->total());
        $this->assertEquals($student1->id, $results->items()[0]->student_id);
    }

    /** @test */
    public function it_searches_enrollments_by_student_id()
    {
        // Arrange
        $student1 = Student::factory()->create();
        $student2 = Student::factory()->create();

        $enrollment1 = Enrollment::factory()->create(['student_id' => $student1->id]);
        $enrollment2 = Enrollment::factory()->create(['student_id' => $student2->id]);

        // Act
        $results = $this->service->searchEnrollments(['student_id' => $student1->id]);

        // Assert
        $this->assertEquals(1, $results->total());
        $this->assertEquals($enrollment1->id, $results->items()[0]->id);
    }

    /** @test */
    public function it_searches_enrollments_by_guardian_id()
    {
        // Arrange
        $guardian1 = Guardian::factory()->create();
        $guardian2 = Guardian::factory()->create();

        $enrollment1 = Enrollment::factory()->create(['guardian_id' => $guardian1->id]);
        $enrollment2 = Enrollment::factory()->create(['guardian_id' => $guardian2->id]);

        // Act
        $results = $this->service->searchEnrollments(['guardian_id' => $guardian1->id]);

        // Assert
        $this->assertEquals(1, $results->total());
        $this->assertEquals($enrollment1->id, $results->items()[0]->id);
    }

    /** @test */
    public function it_searches_enrollments_by_classroom_id()
    {
        // Arrange
        $classroom1 = Classroom::factory()->create();
        $classroom2 = Classroom::factory()->create();

        $enrollment1 = Enrollment::factory()->create(['classroom_id' => $classroom1->id]);
        $enrollment2 = Enrollment::factory()->create(['classroom_id' => $classroom2->id]);

        // Act
        $results = $this->service->searchEnrollments(['classroom_id' => $classroom1->id]);

        // Assert
        $this->assertEquals(1, $results->total());
        $this->assertEquals($enrollment1->id, $results->items()[0]->id);
    }

    /** @test */
    public function it_searches_enrollments_by_status()
    {
        // Arrange
        $activeEnrollment = Enrollment::factory()->create(['status' => 'active']);
        $pendingEnrollment = Enrollment::factory()->create(['status' => 'pending']);
        $cancelledEnrollment = Enrollment::factory()->create(['status' => 'cancelled']);

        // Act
        $results = $this->service->searchEnrollments(['status' => 'active']);

        // Assert
        $this->assertEquals(1, $results->total());
        $this->assertEquals($activeEnrollment->id, $results->items()[0]->id);
    }

    /** @test */
    public function it_searches_enrollments_with_multiple_filters()
    {
        // Arrange
        $student = Student::factory()->create(['name' => 'João Silva']);
        $guardian = Guardian::factory()->create();
        $classroom = Classroom::factory()->create();

        $targetEnrollment = Enrollment::factory()->create([
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'classroom_id' => $classroom->id,
            'status' => 'active',
        ]);

        // Create noise data
        Enrollment::factory()->create(['status' => 'pending']);
        Enrollment::factory()->create(['status' => 'cancelled']);

        // Act
        $results = $this->service->searchEnrollments([
            'student' => 'Silva',
            'status' => 'active',
            'guardian_id' => $guardian->id,
        ]);

        // Assert
        $this->assertEquals(1, $results->total());
        $this->assertEquals($targetEnrollment->id, $results->items()[0]->id);
    }
} 