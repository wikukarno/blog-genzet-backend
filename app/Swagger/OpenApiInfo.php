<?php


namespace App\Swagger;

/**
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter token in format (Bearer {token})"
 * )
 */


/**
 * @OA\Info(
 *     title="API Home Test Article",
 *     version="1.0.0",
 *     description="Dokumentasi API untuk Blog Genzet",
 *     @OA\Contact(
 *         email="hi@wikukarno.com",
 *         name="Developer"
 *     )
 * )
 */
class OpenApiInfo {}
