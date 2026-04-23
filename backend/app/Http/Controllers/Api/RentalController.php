<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rental;
use App\Models\Reservation;
use App\Services\RentalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class RentalController extends Controller
{
    public function __construct(
        protected RentalService $rentalService
    ) {
    }

    #[OA\Post(
        path: "/rentals/checkout",
        summary: "Realizar check-out (Iniciar locação)",
        security: [["sanctum" => []]],
        tags: ["Rentals"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["reservation_id"],
                properties: [
                    new OA\Property(property: "reservation_id", type: "string", example: "uuid-reservation")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Locação iniciada")
        ]
    )]
    public function checkout(Request $request): JsonResponse
    {
        $data = $request->validate([
            'reservation_id' => ['required', 'exists:reservations,id'],
        ]);

        $reservation = Reservation::findOrFail($data['reservation_id']);

        $this->authorize('update', $reservation);

        $rental = $this->rentalService->checkout($reservation);

        return response()->json($rental, 201);
    }

    #[OA\Post(
        path: "/rentals/{rental}/checkin",
        summary: "Realizar check-in (Devolver veículo)",
        security: [["sanctum" => []]],
        tags: ["Rentals"],
        parameters: [
            new OA\Parameter(name: "rental", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Locação finalizada e taxas calculadas")
        ]
    )]
    public function checkin(Rental $rental): JsonResponse
    {
        $this->authorize('update', $rental);

        $rental = $this->rentalService->checkin($rental);

        return response()->json($rental);
    }

    #[OA\Get(
        path: "/rentals",
        summary: "Listar minhas locações",
        security: [["sanctum" => []]],
        tags: ["Rentals"],
        responses: [
            new OA\Response(response: 200, description: "Minhas locações")
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $rentals = Rental::where('user_id', $request->user()->id)
            ->with(['vehicle.category', 'payments'])
            ->get();

        return response()->json($rentals);
    }
}
