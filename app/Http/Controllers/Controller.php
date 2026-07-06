<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     title="Pacific Ecommerce API",
 *     version="1.0.0",
 *     description="Gestión de programas y proyectos",
 *     @OA\Contact(
 *         email="info@pacificecommerce.org",
 *         name="Pacific Ecommerce Support"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="https://www.epulse.pro/api/v1",
 *     description="Servidor de Producción"
 * )
 * 
 * @OA\Server(
 *     url="http://pacificecommerce.test/api/v1",
 *     description="Servidor de Desarrollo Local"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="JWT Authorization header. Obtén el token con POST /auth/login. Formato: 'Bearer {token}'"
 * )
 * 
 * @OA\Tag(
 *     name="Authentication",
 *     description="Autenticación y autorización con JWT"
 * )
 * 
 * @OA\Tag(
 *     name="Programs",
 *     description="Gestión de Programas - CRUD completo con relaciones M:N"
 * )
 * 
 * @OA\Tag(
 *     name="Donors",
 *     description="Gestión de Donantes"
 * )
 * 
 * @OA\Tag(
 *     name="Beneficiaries",
 *     description="Gestión de Beneficiarios"
 * )
 * 
 * @OA\Tag(
 *     name="Contacts",
 *     description="Gestión de Contactos"
 * )
 * 
 * @OA\Tag(
 *     name="Countries",
 *     description="Gestión de Países"
 * )
 * 
 * @OA\Tag(
 *     name="Agencies",
 *     description="Gestión de Agencias"
 * )
 * 
 * @OA\Tag(
 *     name="SDGs",
 *     description="Objetivos de Desarrollo Sostenible (ODS)"
 * )
 * 
 * @OA\Tag(
 *     name="Program States",
 *     description="Estados de Programas"
 * )
 */
class Controller extends BaseController
{
    // Base controller for application (modular controllers extend this)
}
