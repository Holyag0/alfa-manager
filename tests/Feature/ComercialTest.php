<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComercialTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Criar um usuário para os testes
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_list_services()
    {
        $response = $this->actingAs($this->user)
            ->get(route('services.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Comercial/Services/Index'));
    }

    /** @test */
    public function it_can_create_service()
    {
        $serviceData = [
            'name' => 'Test Service',
            'price' => 100.00,
            'category' => 'Test Category',
            'status' => 'active',
            'description' => 'Test description'
        ];

        $response = $this->actingAs($this->user)
            ->post(route('services.store'), $serviceData);

        $response->assertRedirect(route('services.index'));
        $this->assertDatabaseHas('services', $serviceData);
    }

    /** @test */
    public function it_can_update_service()
    {
        $service = Service::factory()->create();
        
        $updateData = [
            'name' => 'Updated Service',
            'price' => 150.00,
            'category' => 'Updated Category',
            'status' => 'inactive',
            'description' => 'Updated description'
        ];

        $response = $this->actingAs($this->user)
            ->put(route('services.update', $service), $updateData);

        $response->assertRedirect(route('services.index'));
        $this->assertDatabaseHas('services', $updateData);
    }

    /** @test */
    public function it_can_delete_service()
    {
        $service = Service::factory()->create();

        $response = $this->actingAs($this->user)
            ->delete(route('services.destroy', $service));

        $response->assertRedirect(route('services.index'));
        $this->assertDatabaseMissing('services', ['id' => $service->id]);
    }

    /** @test */
    public function it_can_toggle_service_status()
    {
        $service = Service::factory()->create(['status' => 'active']);

        $response = $this->actingAs($this->user)
            ->get(route('comercial.services.toggle-status', $service));

        $response->assertRedirect(route('services.index'));
        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'status' => 'inactive'
        ]);
    }

    /** @test */
    public function it_can_list_packages()
    {
        $response = $this->actingAs($this->user)
            ->get(route('packages.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Comercial/Packages/Index'));
    }

    /** @test */
    public function it_can_create_package()
    {
        $packageData = [
            'name' => 'Test Package',
            'price' => 200.00,
            'category' => 'Test Category',
            'status' => 'active',
            'description' => 'Test description'
        ];

        $response = $this->actingAs($this->user)
            ->post(route('packages.store'), $packageData);

        $response->assertRedirect(route('packages.index'));
        $this->assertDatabaseHas('packages', $packageData);
    }

    /** @test */
    public function it_can_update_package()
    {
        $package = Package::factory()->create();
        
        $updateData = [
            'name' => 'Updated Package',
            'price' => 250.00,
            'category' => 'Updated Category',
            'status' => 'inactive',
            'description' => 'Updated description'
        ];

        $response = $this->actingAs($this->user)
            ->put(route('packages.update', $package), $updateData);

        $response->assertRedirect(route('packages.index'));
        $this->assertDatabaseHas('packages', $updateData);
    }

    /** @test */
    public function it_can_delete_package()
    {
        $package = Package::factory()->create();

        $response = $this->actingAs($this->user)
            ->delete(route('packages.destroy', $package));

        $response->assertRedirect(route('packages.index'));
        $this->assertDatabaseMissing('packages', ['id' => $package->id]);
    }

    /** @test */
    public function it_can_toggle_package_status()
    {
        $package = Package::factory()->create(['status' => 'active']);

        $response = $this->actingAs($this->user)
            ->get(route('comercial.packages.toggle-status', $package));

        $response->assertRedirect(route('packages.index'));
        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'status' => 'inactive'
        ]);
    }

    /** @test */
    public function it_can_attach_service_to_package()
    {
        $package = Package::factory()->create();
        $service = Service::factory()->create();

        $response = $this->actingAs($this->user)
            ->post(route('comercial.packages.add-service', [$package, $service]));

        $response->assertRedirect(route('packages.edit', $package));
        $this->assertDatabaseHas('package_service', [
            'package_id' => $package->id,
            'service_id' => $service->id
        ]);
    }

    /** @test */
    public function it_can_remove_service_from_package()
    {
        $package = Package::factory()->create();
        $service = Service::factory()->create();
        $package->services()->attach($service->id);

        $response = $this->actingAs($this->user)
            ->delete(route('comercial.packages.remove-service', [$package, $service]));

        $response->assertRedirect(route('packages.edit', $package));
        $this->assertDatabaseMissing('package_service', [
            'package_id' => $package->id,
            'service_id' => $service->id
        ]);
    }

    /** @test */
    public function it_can_get_service_categories()
    {
        $response = $this->actingAs($this->user)
            ->get(route('comercial.services.categories.list'));

        $response->assertStatus(200);
        $response->assertJsonStructure(['categories']);
    }

    /** @test */
    public function it_can_get_package_categories()
    {
        $response = $this->actingAs($this->user)
            ->get(route('comercial.packages.categories.list'));

        $response->assertStatus(200);
        $response->assertJsonStructure(['categories']);
    }

    /** @test */
    public function it_can_get_package_services()
    {
        $package = Package::factory()->create();
        $service = Service::factory()->create();
        $package->services()->attach($service->id);

        $response = $this->actingAs($this->user)
            ->get(route('comercial.packages.services', $package));

        $response->assertStatus(200);
        $response->assertJsonStructure(['services']);
    }
}
