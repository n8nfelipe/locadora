<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vehicle\StoreVehicleRequest;
use App\Http\Resources\VehicleResource;
use App\Models\Vehicle;
use App\Services\VehicleService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class VehicleController extends Controller
{
    public function __construct(
        protected VehicleService $vehicleService
    ) {
    }

    #[OA\Post(
        path: "/admin/vehicles",
        summary: "Criar novo veículo (Admin)",
        security: [["sanctum" => []]],
        tags: ["Admin - Vehicles"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["brand", "model", "category_id", "year", "license_plate"],
                properties: [
                    new OA\Property(property: "brand", type: "string", example: "Toyota"),
                    new OA\Property(property: "model", type: "string", example: "Corolla"),
                    new OA\Property(property: "category_id", type: "string", example: "uuid-category"),
                    new OA\Property(property: "year", type: "integer", example: 2024),
                    new OA\Property(property: "license_plate", type: "string", example: "ABC-1234"),
                    new OA\Property(property: "status", type: "string", example: "available")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Veículo criado"),
            new OA\Response(response: 403, description: "Não autorizado")
        ]
    )]
    public function store(StoreVehicleRequest $request): JsonResponse
    {
        $vehicle = $this->vehicleService->create($request->validated());
        return (new VehicleResource($vehicle))->response()->setStatusCode(201);
    }

    #[OA\Put(
        path: "/admin/vehicles/{vehicle}",
        summary: "Atualizar veículo (Admin)",
        security: [["sanctum" => []]],
        tags: ["Admin - Vehicles"],
        parameters: [
            new OA\Parameter(name: "vehicle", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "brand", type: "string", example: "Toyota"),
                    new OA\Property(property: "model", type: "string", example: "Corolla Hybrid"),
                    new OA\Property(property: "status", type: "string", example: "rented")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Veículo atualizado")
        ]
    )]
    public function update(StoreVehicleRequest $request, Vehicle $vehicle): VehicleResource
    {
        $vehicle = $this->vehicleService->update($vehicle, $request->validated());
        return new VehicleResource($vehicle);
    }

    #[OA\Delete(
        path: "/admin/vehicles/{vehicle}",
        summary: "Remover veículo (Admin)",
        security: [["sanctum" => []]],
        tags: ["Admin - Vehicles"],
        parameters: [
            new OA\Parameter(name: "vehicle", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 204, description: "Veículo removido")
        ]
    )]
    public function destroy(Vehicle $vehicle): JsonResponse
    {
        $this->vehicleService->delete($vehicle);
        return response()->json(null, 204);
    }
}
