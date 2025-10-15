<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ServiceGuardian;
use App\Models\Guardian;
use App\Models\Address;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ServiceGuardianTest extends TestCase
{
    use RefreshDatabase;

    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ServiceGuardian();
    }

    /** @test */
    public function it_creates_guardian_with_complete_address()
    {
        $data = [
            'name' => 'João Silva',
            'cpf' => '12345678901',
            'addresses' => [
                [
                    'type' => 'residencial',
                    'zip_code' => '60351250',
                    'street' => 'Rua Francisco Mendes de Oliveira',
                    'number' => '123',
                    'complement' => 'Apto 101',
                    'neighborhood' => 'Olavo Oliveira',
                    'city' => 'Fortaleza',
                    'state' => 'CE',
                    'address_for' => 'casa',
                    'is_primary' => true,
                ]
            ],
            'contacts' => [
                [
                    'type' => 'phone',
                    'value' => '85999999999',
                    'label' => 'pessoal',
                    'is_primary' => true,
                ]
            ]
        ];

        $guardian = $this->service->create($data);

        $this->assertInstanceOf(Guardian::class, $guardian);
        $this->assertEquals('João Silva', $guardian->name);
        $this->assertEquals('12345678901', $guardian->cpf);

        // Verificar se o endereço foi criado
        $this->assertCount(1, $guardian->addresses);
        $address = $guardian->addresses->first();
        $this->assertEquals('123', $address->number);
        $this->assertEquals('Rua Francisco Mendes de Oliveira', $address->street);

        // Verificar se o contato foi criado
        $this->assertCount(1, $guardian->contacts);
        $contact = $guardian->contacts->first();
        $this->assertEquals('85999999999', $contact->value);
    }

    /** @test */
    public function it_handles_address_with_null_number()
    {
        $data = [
            'name' => 'Maria Santos',
            'cpf' => '98765432100',
            'addresses' => [
                [
                    'type' => 'residencial',
                    'zip_code' => '60351250',
                    'street' => 'Rua Francisco Mendes de Oliveira',
                    'number' => null, // Campo nulo
                    'complement' => '',
                    'neighborhood' => 'Olavo Oliveira',
                    'city' => 'Fortaleza',
                    'state' => 'CE',
                    'address_for' => 'casa',
                    'is_primary' => true,
                ]
            ]
        ];

        $guardian = $this->service->create($data);

        $this->assertInstanceOf(Guardian::class, $guardian);
        
        // Verificar se o endereço foi criado com número vazio
        $this->assertCount(1, $guardian->addresses);
        $address = $guardian->addresses->first();
        $this->assertEquals('', $address->number); // Deve ser string vazia, não null
        $this->assertEquals('Rua Francisco Mendes de Oliveira', $address->street);
    }

    /** @test */
    public function it_handles_contact_with_null_value()
    {
        $data = [
            'name' => 'Pedro Costa',
            'cpf' => '11122233344',
            'contacts' => [
                [
                    'type' => 'phone',
                    'value' => null, // Campo nulo
                    'label' => 'pessoal',
                    'is_primary' => true,
                ]
            ]
        ];

        $guardian = $this->service->create($data);

        $this->assertInstanceOf(Guardian::class, $guardian);
        
        // Contato com valor nulo não deve ser criado
        $this->assertCount(0, $guardian->contacts);
    }

    /** @test */
    public function it_skips_empty_addresses()
    {
        $data = [
            'name' => 'Ana Lima',
            'cpf' => '55566677788',
            'addresses' => [
                [
                    'zip_code' => '', // CEP vazio
                    'street' => '',
                    'number' => '',
                    'complement' => '',
                    'neighborhood' => '',
                    'city' => '',
                    'state' => '',
                    'address_for' => 'casa',
                    'is_primary' => true,
                ]
            ]
        ];

        $guardian = $this->service->create($data);

        $this->assertInstanceOf(Guardian::class, $guardian);
        
        // Endereço vazio não deve ser criado
        $this->assertCount(0, $guardian->addresses);
    }

    /** @test */
    public function it_creates_guardian_without_addresses_or_contacts()
    {
        $data = [
            'name' => 'Carlos Oliveira',
            'cpf' => '99988877766',
        ];

        $guardian = $this->service->create($data);

        $this->assertInstanceOf(Guardian::class, $guardian);
        $this->assertEquals('Carlos Oliveira', $guardian->name);
        $this->assertCount(0, $guardian->addresses);
        $this->assertCount(0, $guardian->contacts);
    }
}
