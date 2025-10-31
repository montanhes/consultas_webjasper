<?php

namespace App\Repositories\Eloquent;

use App\Models\Service;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ServiceRepository implements ServiceRepositoryInterface
{
    public function all(): Collection
    {
        return Service::all();
    }

    public function allPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Service::paginate($perPage);
    }

    public function create(array $data): Service
    {
        return Service::create($data);
    }

    public function findById(int $id): ?Service
    {
        return Service::find($id);
    }

    public function update(int $id, array $data): ?Service
    {
        $service = $this->findById($id);
        if ($service) {
            $service->update($data);
            return $service;
        }
        return null;
    }

    public function delete(int $id): bool
    {
        $service = $this->findById($id);
        if ($service) {
            return $service->delete();
        }
        return false;
    }
}
