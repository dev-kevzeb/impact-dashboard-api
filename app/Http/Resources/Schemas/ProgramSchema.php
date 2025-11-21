<?php

namespace App\Http\Resources\Schemas;

/**
 * @OA\Schema(
 *     schema="Program",
 *     title="Program",
 *     description="Modelo de Programa con todas sus relaciones",
 *     @OA\Property(property="id", type="integer", example=1, description="ID único del programa"),
 *     @OA\Property(property="name", type="string", example="Programa de Educación Rural 2025", description="Nombre del programa"),
 *     @OA\Property(property="description", type="string", example="Programa enfocado en mejorar la educación en zonas rurales", description="Descripción detallada"),
 *     @OA\Property(property="banner_img", type="string", example="program_banners/1731884521_banner.jpg", description="Path de la imagen banner"),
 *     @OA\Property(property="start_date", type="string", format="date", example="2025-01-15", description="Fecha de inicio"),
 *     @OA\Property(property="end_date", type="string", format="date", example="2027-12-31", description="Fecha de fin"),
 *     @OA\Property(property="program_url", type="string", example="https://www.programa-educacion.org", description="URL del sitio web del programa"),
 *     @OA\Property(
 *         property="contact",
 *         ref="#/components/schemas/Contact",
 *         description="Contacto responsable del programa"
 *     ),
 *     @OA\Property(
 *         property="program_state",
 *         ref="#/components/schemas/ProgramState",
 *         description="Estado actual del programa (Inactivo al crear, Activo cuando tiene proyectos)"
 *     ),
 *     @OA\Property(
 *         property="country",
 *         ref="#/components/schemas/Country",
 *         description="País donde opera el programa"
 *     ),
 *     @OA\Property(
 *         property="sdgs",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/SDG"),
 *         description="Objetivos de Desarrollo Sostenible asociados"
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="Contact",
 *     title="Contact",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="first_name", type="string", example="Juan"),
 *     @OA\Property(property="last_name", type="string", example="Pérez"),
 *     @OA\Property(property="title", type="string", example="Director"),
 *     @OA\Property(property="email", type="string", example="juan.perez@example.com"),
 *     @OA\Property(property="phone", type="string", example="+591 77123456")
 * )
 * 
 * @OA\Schema(
 *     schema="ProgramState",
 *     title="ProgramState",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Inactivo", description="Posibles valores: Inactivo, Activo, Finalizado")
 * )
 * 
 * @OA\Schema(
 *     schema="Country",
 *     title="Country",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Bolivia"),
 *     @OA\Property(
 *         property="currency",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="code", type="string", example="BOB")
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="SDG",
 *     title="SDG",
 *     description="Objetivo de Desarrollo Sostenible (ODS)",
 *     @OA\Property(property="id", type="integer", example=4),
 *     @OA\Property(property="name", type="string", example="Educación de Calidad"),
 *     @OA\Property(property="description", type="string", example="Garantizar una educación inclusiva, equitativa y de calidad"),
 *     @OA\Property(property="color_hex", type="string", example="#C5192D")
 * )
 */
class ProgramSchema
{
    // Esta clase solo contiene anotaciones Swagger, no tiene lógica
}
