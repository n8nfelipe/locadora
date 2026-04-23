<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ReservationController extends Controller
{
    public function __construct(
        protected ReservationService $reservationService
    ) {
    }

    #[OA\Post(
        path: "/reservations",
        summary: "Criar uma reserva",
        security: [["sanctum" => []]],
        tags: ["Reservations"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["vehicle_id", "start_date", "end_date"],
                properties: [
                    new OA\Property(property: "vehicle_id", type: "string", example: "uuid-vehicle"),
                    new OA\Property(property: "start_date", type: "string", format: "date", example: "2024-12-01"),
                    new OA\Property(property: "end_date", type: "string", format: "date", example: "2024-12-05")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Reserva criada"),
            new OA\Response(response: 422, description: "Erro de disponibilidade")
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ]);

        $data['user_id'] = $request->user()->id;

        $reservation = $this->reservationService->create($data);

        return response()->json($reservation, 201);
    }

    #[OA\Get(
        path: "/reservations",
        summary: "Listar minhas reservas",
        security: [["sanctum" => []]],
        tags: ["Reservations"],
        responses: [
            new OA\Response(response: 200, description: "Minhas reservas")
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $reservations = Reservation::where('user_id', $request->user()->id)
            ->with('vehicle.category')
            ->get();

        return response()->json($reservations);
    }

    #[OA\Post(
        path: "/reservations/{reservation}/cancel",
        summary: "Cancelar uma reserva",
        security: [["sanctum" => []]],
        tags: ["Reservations"],
        parameters: [
            new OA\Parameter(name: "reservation", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Reserva cancelada")
        ]
    )]
    public function cancel(Reservation $reservation): JsonResponse
    {
        $this->authorize('update', $reservation);

        $this->reservationService->cancel($reservation);

        return response()->json(['message' => 'Reservation cancelled']);
    }
}
