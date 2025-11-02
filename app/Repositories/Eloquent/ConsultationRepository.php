<?php

namespace App\Repositories\Eloquent;

use App\Events\ConsultationModified;
use App\Models\Consultation;
use App\Models\User;
use App\Repositories\Contracts\ConsultationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class ConsultationRepository implements ConsultationRepositoryInterface
{
    public function allForUser(User $user): Collection
    {
        return $user->consultations()->with('services')->get();
    }

    public function allForUserPaginated(User $user, int $perPage = 15): LengthAwarePaginator
    {
        $page = request('page', 1);
        $cacheKey = "user.{$user->id}.consultations.page.{$page}.perpage.{$perPage}";

        return Cache::tags(["user.{$user->id}.consultations"])
            ->remember($cacheKey, now()->addMinutes(60), function () use ($user, $perPage) {
                return $user->consultations()->with('services', 'user')->paginate($perPage);
            });
    }

    public function createForUser(User $user, array $data): Consultation
    {
        $consultation = $user->consultations()->create($data);

        if (isset($data['service_ids'])) {
            $consultation->services()->attach($data['service_ids']);
            $consultation->total_price = $consultation->services()->sum('price');
            $consultation->save();
        }

        Cache::tags(["user.{$user->id}.consultations"])->flush();
        event(new ConsultationModified($consultation));

        return $consultation;
    }

    public function findById(int $id): ?Consultation
    {
        return Consultation::with('services', 'user')->find($id);
    }

    public function update(int $id, array $data): ?Consultation
    {
        $consultation = $this->findById($id);
        if ($consultation) {
            $consultation->update($data);

            if (isset($data['service_ids'])) {
                $consultation->services()->sync($data['service_ids']);
                $consultation->total_price = $consultation->services()->sum('price');
                $consultation->save();
            }

            Cache::tags(["user.{$consultation->user_id}.consultations"])->flush();
            event(new ConsultationModified($consultation));
            return $consultation;
        }
        return null;
    }

    public function delete(int $id): bool
    {
        $consultation = $this->findById($id);
        if ($consultation) {
            Cache::tags(["user.{$consultation->user_id}.consultations"])->flush();
            $consultation->delete();

            return true;
        }
        return false;
    }
}
