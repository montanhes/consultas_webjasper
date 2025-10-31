<?php

namespace App\Repositories\Eloquent;

use App\Models\Consultation;
use App\Models\User;
use App\Repositories\Contracts\ConsultationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ConsultationRepository implements ConsultationRepositoryInterface
{
    public function allForUser(User $user): Collection
    {
        return $user->consultations()->with('services')->get();
    }

    public function allForUserPaginated(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $user->consultations()->with('services')->paginate($perPage);
    }

    public function createForUser(User $user, array $data): Consultation
    {
        return $user->consultations()->create($data);
    }

    public function findById(int $id): ?Consultation
    {
        return Consultation::with('services')->find($id);
    }

    public function update(int $id, array $data): ?Consultation
    {
        $consultation = $this->findById($id);
        if ($consultation) {
            $consultation->update($data);
            return $consultation;
        }
        return null;
    }

    public function delete(int $id): bool
    {
        $consultation = $this->findById($id);
        if ($consultation) {
            return $consultation->delete();
        }
        return false;
    }
}
