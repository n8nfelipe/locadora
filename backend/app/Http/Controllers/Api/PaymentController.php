<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rental;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {
    }

    #[OA\Post(
        path: "/rentals/{rental}/pay",
        summary: "Realizar pagamento de locação",
        security: [["sanctum" => []]],
        tags: ["Payments"],
        parameters: [
            new OA\Parameter(name: "rental", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["payment_method"],
                properties: [
                    new OA\Property(property: "payment_method", type: "string", example: "credit_card")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Pagamento realizado")
        ]
    )]
    public function pay(Request $request, Rental $rental): JsonResponse
    {
        $this->authorize('update', $rental);

        $data = $request->validate([
            'payment_method' => ['required', 'string'],
        ]);

        $payment = $this->paymentService->pay($rental, $data['payment_method']);

        return response()->json($payment, 201);
    }
}
