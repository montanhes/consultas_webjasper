<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('unauthenticated user cannot access service endpoints', function () {
    $this->getJson('/api/services')->assertUnauthorized();
});

test('can list services with pagination', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    Service::factory()->count(20)->create();

    $this->getJson('/api/services?per_page=10')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'price']
            ],
            'links',
            'meta' => ['current_page', 'per_page', 'total']
        ])
        ->assertJsonCount(10, 'data');
});

test('can create a new service', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $serviceData = [
        'name' => 'New Test Service',
        'price' => 99.99
    ];

    $this->postJson('/api/services', $serviceData)
        ->assertCreated()
        ->assertJsonFragment($serviceData);

    $this->assertDatabaseHas('services', $serviceData);
});

test('cannot create a service with invalid data', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/services', ['name' => ''])
        ->assertStatus(422)
        ->assertJsonValidationErrors('name');
});

test('cannot create a service with a duplicate name', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    Service::factory()->create(['name' => 'Existing Service']);

    $serviceData = [
        'name' => 'Existing Service',
        'price' => 10.00
    ];

    $this->postJson('/api/services', $serviceData)
        ->assertStatus(422)
        ->assertJsonValidationErrors('name');
});

test('can show a specific service', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $service = Service::factory()->create();

    $this->getJson("/api/services/{$service->id}")
        ->assertOk()
        ->assertJsonFragment(['id' => $service->id]);
});

test('returns 404 when showing a non-existent service', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/services/999')
        ->assertNotFound();
});

test('can update a service', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $service = Service::factory()->create();

    $updateData = [
        'name' => 'Updated Service Name',
        'price' => 123.45
    ];

    $this->putJson("/api/services/{$service->id}", $updateData)
        ->assertOk()
        ->assertJsonFragment($updateData);

    $this->assertDatabaseHas('services', $updateData);
});

test('cannot update a service with a duplicate name', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    Service::factory()->create(['name' => 'Existing Service']);
    $serviceToUpdate = Service::factory()->create(['name' => 'Another Service']);

    $updateData = [
        'name' => 'Existing Service',
        'price' => 20.00
    ];

    $this->putJson("/api/services/{$serviceToUpdate->id}", $updateData)
        ->assertStatus(422)
        ->assertJsonValidationErrors('name');
});

test('can delete a service', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $service = Service::factory()->create();

    $this->deleteJson("/api/services/{$service->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('services', ['id' => $service->id]);
});

test('returns 404 when deleting a non-existent service', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->deleteJson('/api/services/999')
        ->assertNotFound();
});

test('can list services when database is empty', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/services')
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

test('pagination handles invalid per_page parameter gracefully', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    Service::factory()->count(5)->create();

    $this->getJson('/api/services?per_page=abc')
        ->assertOk()
        ->assertJsonStructure([
            'data',
            'meta' => ['per_page']
        ])
        ->assertJsonFragment(['per_page' => 15]);

    $this->getJson('/api/services?per_page=-5')
        ->assertOk()
        ->assertJsonStructure([
            'data',
            'meta' => ['per_page']
        ])
        ->assertJsonFragment(['per_page' => 15]);
});

test('cannot create a service with non-string name', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $serviceData = [
        'name' => ['invalid'],
        'price' => 10.00
    ];

    $this->postJson('/api/services', $serviceData)
        ->assertStatus(422)
        ->assertJsonValidationErrors('name');
});

test('cannot create a service with non-numeric price', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $serviceData = [
        'name' => 'Test Service',
        'price' => 'invalid_price'
    ];

    $this->postJson('/api/services', $serviceData)
        ->assertStatus(422)
        ->assertJsonValidationErrors('price');
});

test('cannot update a service with non-string name', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);
    $service = Service::factory()->create();

    $updateData = [
        'name' => ['invalid'],
        'price' => 10.00
    ];

    $this->putJson("/api/services/{$service->id}", $updateData)
        ->assertStatus(422)
        ->assertJsonValidationErrors('name');
});

test('cannot update a service with non-numeric price', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);
    $service = Service::factory()->create();

    $updateData = [
        'name' => 'Test Service',
        'price' => 'invalid_price'
    ];

    $this->putJson("/api/services/{$service->id}", $updateData)
        ->assertStatus(422)
        ->assertJsonValidationErrors('price');
});
