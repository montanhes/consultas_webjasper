<?php

namespace App\Repositories\Contracts;

use App\Models\Consultation;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ConsultationRepositoryInterface
{
    public function allForUser(User $user): Collection;

    public function allForUserPaginated(User $user, int $perPage = 15): LengthAwarePaginator;

    public function createForUser(User $user, array $data): Consultation;

    public function findById(int $id): ?Consultation;

    public function update(int $id, array $data): ?Consultation;

    public function delete(int $id): bool;
}
