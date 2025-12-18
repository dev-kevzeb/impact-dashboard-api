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
 *     @OA\Property(property="banner_img", type="string", example="program_banners/1731884521_banner.jpg", nullable=true, description="Path de la imagen banner (opcional)"),
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
 *         property="sdgs",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/SDG"),
 *         description="Objetivos de Desarrollo Sostenible asociados"
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
