<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\ServiceGuardian;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\Contact;
use App\Models\Address;

class ServiceGuardianTest extends TestCase
{
    use RefreshDatabase;

    protected ServiceGuardian $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ServiceGuardian();
    }

    /** @test */
    public function it_creates_guardian_with_complete_data()
    {
        // Arrange
        $data = [
            'name' => 'João Silva',
            'cpf' => '12345678901',
            'email' => 'joao@email.com',
            'phone' => '(11) 99999-9999',
            'guardian_type' => 'titular',
            'relationship' => 'pai',
            'occupation' => 'Engenheiro',
            'addresses' => [
                [
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
                    'value' => 'joao.trabalho@email.com',
                    'label' => 'trabalho',
                    'is_primary' => false,
                ]
            ]
        ];

        // Act
        $guardian = $this->service->create($data);

        // Assert
        $this->assertInstanceOf(Guardian::class, $guardian);
        $this->assertEquals('João Silva', $guardian->name);
        $this->assertEquals('12345678901', $guardian->cpf);
        $this->assertEquals('titular', $guardian->guardian_type);

        // Check database
        $this->assertDatabaseHas('guardians', [
            'name' => 'João Silva',
            'cpf' => '12345678901',
            'guardian_type' => 'titular',
        ]);

        // Check contacts were created
        $this->assertDatabaseHas('contacts', [
            'contactable_id' => $guardian->id,
            'contactable_type' => Guardian::class,
            'type' => 'email',
            'value' => 'joao@email.com',
            'is_primary' => true,
        ]);

        $this->assertDatabaseHas('contacts', [
            'contactable_id' => $guardian->id,
            'contactable_type' => Guardian::class,
            'type' => 'phone',
            'value' => '(11) 99999-9999',
            'is_primary' => true,
        ]);

        // Check additional contact
        $this->assertDatabaseHas('contacts', [
            'contactable_id' => $guardian->id,
            'contactable_type' => Guardian::class,
            'type' => 'email',
            'value' => 'joao.trabalho@email.com',
            'is_primary' => false,
        ]);

        // Check address was created
        $this->assertDatabaseHas('addresses', [
            'addressable_id' => $guardian->id,
            'addressable_type' => Guardian::class,
            'street' => 'Rua das Flores',
            'number' => '123',
            'is_primary' => true,
        ]);
    }

    /** @test */
    public function it_creates_guardian_with_minimal_data()
    {
        // Arrange
        $data = [
            'name' => 'Maria Santos',
            'cpf' => '98765432100',
            'email' => 'maria@email.com',
            'phone' => '(11) 88888-8888',
        ];

        // Act
        $guardian = $this->service->create($data);

        // Assert
        $this->assertInstanceOf(Guardian::class, $guardian);
        $this->assertEquals('Maria Santos', $guardian->name);
        $this->assertEquals('titular', $guardian->guardian_type); // Default value

        // Check primary contacts were created
        $this->assertDatabaseHas('contacts', [
            'contactable_id' => $guardian->id,
            'type' => 'email',
            'value' => 'maria@email.com',
            'is_primary' => true,
        ]);

        $this->assertDatabaseHas('contacts', [
            'contactable_id' => $guardian->id,
            'type' => 'phone',
            'value' => '(11) 88888-8888',
            'is_primary' => true,
        ]);
    }

    /** @test */
    public function it_updates_guardian_successfully()
    {
        // Arrange
        $guardian = Guardian::factory()->complete()->create([
            'name' => 'Pedro Oliveira',
            'occupation' => 'Professor',
        ]);

        $updateData = [
            'name' => 'Pedro Silva Oliveira',
            'occupation' => 'Diretor',
            'workplace' => 'Escola ABC',
        ];

        // Act
        $updatedGuardian = $this->service->update($guardian, $updateData);

        // Assert
        $this->assertEquals('Pedro Silva Oliveira', $updatedGuardian->name);
        $this->assertEquals('Diretor', $updatedGuardian->occupation);
        $this->assertEquals('Escola ABC', $updatedGuardian->workplace);

        $this->assertDatabaseHas('guardians', [
            'id' => $guardian->id,
            'name' => 'Pedro Silva Oliveira',
            'occupation' => 'Diretor',
        ]);
    }

    /** @test */
    public function it_finds_guardian_by_id()
    {
        // Arrange
        $guardian = Guardian::factory()->create();

        // Act
        $found = $this->service->find($guardian->id);

        // Assert
        $this->assertInstanceOf(Guardian::class, $found);
        $this->assertEquals($guardian->id, $found->id);
    }

    /** @test */
    public function it_returns_null_when_guardian_not_found()
    {
        // Act
        $found = $this->service->find(999);

        // Assert
        $this->assertNull($found);
    }

    /** @test */
    public function it_attaches_guardian_to_student()
    {
        // Arrange
        $guardian = Guardian::factory()->create();
        $student = Student::factory()->create();

        // Act
        $result = $this->service->attachToStudent($guardian->id, $student->id);

        // Assert
        $this->assertInstanceOf(Guardian::class, $result);
        $this->assertTrue($student->guardians->contains($guardian));
        
        $this->assertDatabaseHas('guardian_student', [
            'guardian_id' => $guardian->id,
            'student_id' => $student->id,
        ]);
    }

    /** @test */
    public function it_prevents_duplicate_guardian_student_attachment()
    {
        // Arrange
        $guardian = Guardian::factory()->create();
        $student = Student::factory()->create();

        // First attachment
        $this->service->attachToStudent($guardian->id, $student->id);

        // Act & Assert - Second attachment should not create duplicate
        $result = $this->service->attachToStudent($guardian->id, $student->id);
        
        $this->assertInstanceOf(Guardian::class, $result);
        
        // Should still have only one relationship
        $this->assertEquals(1, $student->guardians()->count());
    }

    /** @test */
    public function it_detaches_guardian_from_student()
    {
        // Arrange
        $guardian = Guardian::factory()->create();
        $student = Student::factory()->create();
        
        // First attach
        $student->guardians()->attach($guardian->id);
        $this->assertTrue($student->guardians->contains($guardian));

        // Act
        $result = $this->service->detachFromStudent($guardian->id, $student->id);

        // Assert
        $this->assertInstanceOf(Guardian::class, $result);
        $this->assertFalse($student->fresh()->guardians->contains($guardian));
        
        $this->assertDatabaseMissing('guardian_student', [
            'guardian_id' => $guardian->id,
            'student_id' => $student->id,
        ]);
    }

    /** @test */
    public function it_searches_guardians_not_linked_to_student()
    {
        // Arrange
        $student = Student::factory()->create();
        
        $linkedGuardian = Guardian::factory()->create(['name' => 'João Silva']);
        $unlinkedGuardian1 = Guardian::factory()->create(['name' => 'Maria Silva']);
        $unlinkedGuardian2 = Guardian::factory()->create(['name' => 'Pedro Santos']);

        // Link one guardian
        $student->guardians()->attach($linkedGuardian->id);

        // Act
        $results = $this->service->searchNotLinkedToStudent($student->id, 'Silva');

        // Assert
        $this->assertCount(1, $results);
        $this->assertEquals($unlinkedGuardian1->id, $results->first()->id);
        $this->assertFalse($results->contains($linkedGuardian));
    }

    /** @test */
    public function it_searches_guardians_by_name()
    {
        // Arrange
        Guardian::factory()->create(['name' => 'João Silva']);
        Guardian::factory()->create(['name' => 'Maria Silva']);
        Guardian::factory()->create(['name' => 'Pedro Santos']);

        // Act
        $results = $this->service->search('Silva');

        // Assert
        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('name', 'João Silva'));
        $this->assertTrue($results->contains('name', 'Maria Silva'));
        $this->assertFalse($results->contains('name', 'Pedro Santos'));
    }

    /** @test */
    public function it_searches_guardians_by_cpf()
    {
        // Arrange
        Guardian::factory()->create(['cpf' => '12345678901']);
        Guardian::factory()->create(['cpf' => '12345678902']);
        Guardian::factory()->create(['cpf' => '98765432100']);

        // Act
        $results = $this->service->search('123456789');

        // Assert
        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('cpf', '12345678901'));
        $this->assertTrue($results->contains('cpf', '12345678902'));
        $this->assertFalse($results->contains('cpf', '98765432100'));
    }

    /** @test */
    public function it_deletes_guardian_successfully()
    {
        // Arrange
        $guardian = Guardian::factory()->complete()->create();

        // Act
        $result = $this->service->delete($guardian);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseMissing('guardians', ['id' => $guardian->id]);
    }

    /** @test */
    public function it_searches_guardians_with_empty_term()
    {
        // Arrange
        Guardian::factory()->create(['name' => 'João Silva']);
        Guardian::factory()->create(['name' => 'Maria Santos']);

        // Act
        $results = $this->service->search();

        // Assert
        $this->assertCount(2, $results);
    }

    /** @test */
    public function it_limits_search_results_to_10()
    {
        // Arrange
        Guardian::factory()->count(15)->create(['name' => 'Test Guardian']);

        // Act
        $results = $this->service->search('Test');

        // Assert
        $this->assertCount(10, $results);
    }
} 