<?php

namespace Tests\Feature;

use App\Models\Consultation;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

test('notification event is dispatched on consultation creation', function () {
    Event::fake();

    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $consultationData = [
        'title' => 'New Consultation for Event Test',
        'scheduled_at' => now()->addDays(5)->toDateTimeString(),
    ];

    $this->postJson('/api/consultations', $consultationData);

    Event::assertDispatched(\App\Events\ConsultationModified::class);
});

test('notification event is dispatched on consultation update', function () {
    Event::fake();

    $user = User::factory()->create();
    Sanctum::actingAs($user);
    $consultation = Consultation::factory()->create(['user_id' => $user->id]);

    $this->putJson("/api/consultations/{$consultation->id}", ['title' => 'Updated Title']);

    Event::assertDispatched(\App\Events\ConsultationModified::class);
});

test('unauthenticated user cannot access consultation endpoints', function () {
    $this->getJson('/api/consultations')->assertUnauthorized();
});

test('user can only list their own consultations', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    Consultation::factory()->count(3)->create(['user_id' => $userA->id]);
    Consultation::factory()->count(2)->create(['user_id' => $userB->id]);

    Sanctum::actingAs($userA);

    $this->getJson('/api/consultations')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

test('can create a new consultation', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $consultationData = [
        'title' => 'New Consultation',
        'scheduled_at' => now()->addDays(5)->toDateTimeString(),
    ];

    $this->postJson('/api/consultations', $consultationData)
        ->assertCreated()
        ->assertJsonFragment(['title' => 'New Consultation']);

    $this->assertDatabaseHas('consultations', [
        'user_id' => $user->id,
        'title' => 'New Consultation',
    ]);
});

test('can create a consultation with services and total price is calculated', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $service1 = Service::factory()->create(['price' => 50.00]);
    $service2 = Service::factory()->create(['price' => 100.50]);

    $consultationData = [
        'title' => 'Consultation with Services',
        'scheduled_at' => now()->addDays(6)->toDateTimeString(),
        'service_ids' => [$service1->id, $service2->id],
    ];

    $this->postJson('/api/consultations', $consultationData)
        ->assertCreated()
        ->assertJsonFragment(['total_price' => 150.50]);

    $this->assertDatabaseHas('consultations', ['title' => 'Consultation with Services', 'total_price' => 150.50]);
});

test('cannot create a consultation at the same time for the same user', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $scheduledTime = now()->addDay()->startOfHour()->toDateTimeString();
    Consultation::factory()->create(['user_id' => $user->id, 'scheduled_at' => $scheduledTime]);

    $consultationData = [
        'title' => 'Another Consultation',
        'scheduled_at' => $scheduledTime,
    ];

    $this->postJson('/api/consultations', $consultationData)
        ->assertStatus(422)
        ->assertJsonValidationErrors('scheduled_at');
});

test('user can show their own consultation', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $consultation = Consultation::factory()->create(['user_id' => $user->id]);

    $this->getJson("/api/consultations/{$consultation->id}")
        ->assertOk()
        ->assertJsonFragment(['id' => $consultation->id]);
});

test('user cannot show another user\'s consultation', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    Sanctum::actingAs($userA);

    $consultationOfUserB = Consultation::factory()->create(['user_id' => $userB->id]);

    $this->getJson("/api/consultations/{$consultationOfUserB->id}")
        ->assertForbidden();
});

test('user can update their own consultation', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $consultation = Consultation::factory()->create(['user_id' => $user->id]);

    $updateData = ['title' => 'Updated Title'];

    $this->putJson("/api/consultations/{$consultation->id}", $updateData)
        ->assertOk()
        ->assertJsonFragment($updateData);
});

test('user cannot update another user\'s consultation', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    Sanctum::actingAs($userA);

    $consultationOfUserB = Consultation::factory()->create(['user_id' => $userB->id]);

    $this->putJson("/api/consultations/{$consultationOfUserB->id}", ['title' => 'New Title'])
        ->assertForbidden();
});

test('user can cancel their own consultation', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $consultation = Consultation::factory()->create(['user_id' => $user->id, 'status' => 'pending']);

    $this->patchJson("/api/consultations/{$consultation->id}/cancel")
        ->assertOk()
        ->assertJsonFragment(['status' => 'cancelled']);
});

test('user cannot cancel another user\'s consultation', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    Sanctum::actingAs($userA);

    $consultationOfUserB = Consultation::factory()->create(['user_id' => $userB->id]);

    $this->patchJson("/api/consultations/{$consultationOfUserB->id}/cancel")
        ->assertForbidden();
});

test('cannot cancel an already cancelled consultation', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $consultation = Consultation::factory()->create(['user_id' => $user->id, 'status' => 'cancelled']);

    $this->patchJson("/api/consultations/{$consultation->id}/cancel")
        ->assertBadRequest();
});

test('cannot create consultation with non-existent service_id', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $consultationData = [
        'title' => 'Consultation with Invalid Service',
        'scheduled_at' => now()->addDays(7)->toDateTimeString(),
        'service_ids' => [999],
    ];

    $this->postJson('/api/consultations', $consultationData)
        ->assertStatus(422)
        ->assertJsonValidationErrors('service_ids.0');
});

test('total_price is recalculated when services are removed', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $service = Service::factory()->create(['price' => 100.00]);
    $consultation = Consultation::factory()->create(['user_id' => $user->id]);
    $consultation->services()->attach($service);
    $consultation->update(['total_price' => 100.00]);

    $this->assertEquals(100.00, $consultation->fresh()->total_price);

    $this->putJson("/api/consultations/{$consultation->id}", [
        'title' => 'Updated Title',
        'service_ids' => [],
    ])
        ->assertOk()
        ->assertJsonFragment(['total_price' => 0.00]);

    $this->assertDatabaseHas('consultations', ['id' => $consultation->id, 'total_price' => 0.00]);
});

test('cannot update consultation to a conflicting time', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $time1 = now()->addDays(10)->startOfHour()->toDateTimeString();
    $time2 = now()->addDays(11)->startOfHour()->toDateTimeString();

    Consultation::factory()->create(['user_id' => $user->id, 'scheduled_at' => $time1]);
    $consultationToUpdate = Consultation::factory()->create(['user_id' => $user->id, 'scheduled_at' => $time2]);

    $this->putJson("/api/consultations/{$consultationToUpdate->id}", [
        'scheduled_at' => $time1,
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('scheduled_at');
});

test('index returns an empty list for a user with no consultations', function () {
    $userWithNoConsultations = User::factory()->create();
    User::factory()->has(Consultation::factory()->count(3))->create();

    Sanctum::actingAs($userWithNoConsultations);

    $this->getJson('/api/consultations')
        ->assertOk()
        ->assertJsonCount(0, 'data');
});
