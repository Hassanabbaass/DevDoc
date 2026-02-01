<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(title: "DevDoc API", version: "1.0.0")]
#[OA\Server(url: 'http://localhost:8000', description: "Local Server")]
#[OA\SecurityScheme( // [!code hl:7]
    securityScheme: 'bearerAuth',
    type: 'http',
    name: 'Authorization',
    in: 'header',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
abstract class Controller
{
    //
}