<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VehicleResource;
use App\Services\VehicleService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class VehicleController extends Controller
{
    public function __construct(
        protected VehicleService $vehicleService
    ) {
    }

    #[OA\Get(
        path: "/vehicles",
        summary: "Listar todos os veículos",
        security: [["sanctum" => []]],
        tags: ["Vehicles"],
        responses: [
            new OA\Response(response: 200, description: "Lista de veículos paginada")
        ]
    )]
    public function index(Request $request)
    {
        $vehicles = $this->vehicleService->getAll($request->all());
        return VehicleResource::collection($vehicles);
    }

    #[OA\Get(
        path: "/vehicles/available",
        summary: "Listar veículos disponíveis (Cache)",
        security: [["sanctum" => []]],
        tags: ["Vehicles"],
        responses: [
            new OA\Response(response: 200, description: "Lista de veículos disponíveis")
        ]
    )]
    public function available()
    {
        $vehicles = $this->vehicleService->getAvailable();
        return VehicleResource::collection($vehicles);
    }

    #[OA\Get(
        path: "/vehicles/{id}",
        summary: "Obter detalhes de um veículo",
        security: [["sanctum" => []]],
        tags: ["Vehicles"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Detalhes do veículo"),
            new OA\Response(response: 404, description: "Veículo não encontrado")
        ]
    )]
    public function show(string $id)
    {
        $vehicle = \App\Models\Vehicle::with('category')->findOrFail($id);
        return new VehicleResource($vehicle);
    }
}
