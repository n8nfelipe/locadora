<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Locadora API",
    version: "1.0.0",
    description: "API para sistema de locadora de veículos",
    contact: new OA\Contact(email: "admin@locadora.com")
)]
#[OA\Server(
    url: "/api",
    description: "Local API Server"
)]
#[OA\SecurityScheme(
    securityScheme: "sanctum",
    type: "http",
    scheme: "bearer"
)]
class OpenApi
{
}
