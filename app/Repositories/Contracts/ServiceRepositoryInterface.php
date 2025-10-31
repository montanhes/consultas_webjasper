<?php

namespace App\Repositories\Contracts;

use App\Models\Service;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ServiceRepositoryInterface
{
    public function all(): Collection;

    public function allPaginated(int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): Service;

    public function findById(int $id): ?Service;

    public function update(int $id, array $data): ?Service;

    public function delete(int $id): bool;
}
