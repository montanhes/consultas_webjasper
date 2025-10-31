<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @property-read \App\Repositories\Contracts\ServiceRepositoryInterface $serviceRepository
 */
class ServiceController extends Controller
{
    public function __construct(protected ServiceRepositoryInterface $serviceRepository) {}

    /**
     * Display a listing of the services.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        if ($perPage <= 0) {
            $perPage = 15;
        }
        $services = $this->serviceRepository->allPaginated($perPage);
        return ServiceResource::collection($services)->response();
    }

    /**
     * Store a newly created service.
     */
    public function store(StoreServiceRequest $request): JsonResponse
    {
        $service = $this->serviceRepository->create($request->validated());
        return (new ServiceResource($service))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified service.
     */
    public function show(int $id): JsonResponse
    {
        $service = $this->serviceRepository->findById($id);

        if (!$service) {
            return response()->json(['message' => 'Recurso não encontrado.'], Response::HTTP_NOT_FOUND);
        }

        return (new ServiceResource($service))->response();
    }

    /**
     * Update the specified service.
     */
    public function update(UpdateServiceRequest $request, int $id): JsonResponse
    {
        $service = $this->serviceRepository->findById($id);

        if (!$service) {
            return response()->json(['message' => 'Recurso não encontrado.'], Response::HTTP_NOT_FOUND);
        }

        $updatedService = $this->serviceRepository->update($id, $request->validated());
        return (new ServiceResource($updatedService))->response();
    }

    /**
     * Remove the specified service.
     */
    public function destroy(int $id): JsonResponse
    {
        $service = $this->serviceRepository->findById($id);

        if (!$service) {
            return response()->json(['message' => 'Recurso não encontrado.'], Response::HTTP_NOT_FOUND);
        }

        $this->serviceRepository->delete($id);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
