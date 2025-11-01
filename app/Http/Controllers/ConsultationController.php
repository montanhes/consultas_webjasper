<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreConsultationRequest;
use App\Http\Requests\UpdateConsultationRequest;
use App\Http\Resources\ConsultationResource;
use App\Repositories\Contracts\ConsultationRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @property-read \App\Repositories\Contracts\ConsultationRepositoryInterface $consultationRepository
 */
class ConsultationController extends Controller
{
    public function __construct(protected ConsultationRepositoryInterface $consultationRepository) {}

    /**
     * Display a listing of the consultations.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $perPage = $request->query('per_page', 15);
        $consultations = $this->consultationRepository->allForUserPaginated($user, (int)$perPage);
        return ConsultationResource::collection($consultations)->response();
    }

    /**
     * Store a newly created consultation.
     */
    public function store(StoreConsultationRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $user = Auth::user();

        $consultation = $this->consultationRepository->createForUser($user, [
            'title' => $validatedData['title'],
            'scheduled_at' => $validatedData['scheduled_at'],
            'status' => 'pending',
        ]);

        if (isset($validatedData['service_ids'])) {
            $consultation->services()->attach($validatedData['service_ids']);

            $consultation->total_price = $consultation->services()->sum('price');
            $consultation->save();
        }

        $consultation->load('services', 'user');

        return (new ConsultationResource($consultation))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified consultation.
     */
    public function show(int $id): JsonResponse
    {
        $consultation = $this->consultationRepository->findById($id);

        if (!$consultation || $consultation->user_id !== Auth::id()) {
            return response()->json(['message' => 'Recurso não encontrado ou não autorizado.'], Response::HTTP_NOT_FOUND);
        }

        $consultation->load('services', 'user');

        return (new ConsultationResource($consultation))->response();
    }

    /**
     * Update the specified consultation.
     */
    public function update(UpdateConsultationRequest $request, int $id): JsonResponse
    {
        $consultation = $this->consultationRepository->findById($id);

        if (!$consultation || $consultation->user_id !== Auth::id()) {
            return response()->json(['message' => 'Recurso não encontrado ou não autorizado.'], Response::HTTP_NOT_FOUND);
        }

        $validatedData = $request->validated();

        $this->consultationRepository->update($consultation->id, $validatedData);

        if (isset($validatedData['service_ids'])) {
            $consultation->services()->sync($validatedData['service_ids']);

            $consultation->total_price = $consultation->services()->sum('price');
            $consultation->save();
        }

        $updatedConsultation = $this->consultationRepository->findById($consultation->id);
        $updatedConsultation->load('services', 'user');

        return (new ConsultationResource($updatedConsultation))->response();
    }

    /**
     * Cancel the specified consultation.
     */
    public function cancel(int $id): JsonResponse
    {
        $consultation = $this->consultationRepository->findById($id);

        if (!$consultation || $consultation->user_id !== Auth::id()) {
            return response()->json(['message' => 'Recurso não encontrado ou não autorizado.'], Response::HTTP_NOT_FOUND);
        }

        if ($consultation->status === 'cancelled') {
            return response()->json(['message' => 'A consulta já está cancelada.'], Response::HTTP_BAD_REQUEST);
        }

        $this->consultationRepository->update($consultation->id, ['status' => 'cancelled']);
        $consultation->load('services', 'user');

        return response()->json(['message' => 'Consulta cancelada com sucesso.', 'consultation' => new ConsultationResource($consultation->fresh())]);
    }
}
