<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreConsultationRequest;
use App\Http\Requests\UpdateConsultationRequest;
use App\Http\Resources\ConsultationResource;
use App\Models\Consultation;
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
        $consultation = $this->consultationRepository->createForUser(Auth::user(), $request->validated());

        $consultation->load('services', 'user');

        return (new ConsultationResource($consultation))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified consultation.
     */
    public function show(Consultation $consultation): JsonResponse
    {
        $this->authorize('view', $consultation);

        $consultation->load('services', 'user');

        return (new ConsultationResource($consultation))->response();
    }

    /**
     * Update the specified consultation.
     */
    public function update(UpdateConsultationRequest $request, Consultation $consultation): JsonResponse
    {
        $this->authorize('update', $consultation);

        $updatedConsultation = $this->consultationRepository->update($consultation->id, $request->validated());

        $updatedConsultation->load('services', 'user');

        return (new ConsultationResource($updatedConsultation))->response();
    }

    /**
     * Cancel the specified consultation.
     */
    public function cancel(Consultation $consultation): JsonResponse
    {
        $this->authorize('cancel', $consultation);

        if ($consultation->status === 'cancelled') {
            return response()->json(['message' => 'A consulta já está cancelada.'], Response::HTTP_BAD_REQUEST);
        }

        $this->consultationRepository->update($consultation->id, ['status' => 'cancelled']);
        $consultation->load('services', 'user');

        return response()->json(['message' => 'Consulta cancelada com sucesso.', 'consultation' => new ConsultationResource($consultation->fresh())]);
    }
}
