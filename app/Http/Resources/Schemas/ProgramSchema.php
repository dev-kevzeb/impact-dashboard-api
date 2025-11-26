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
 *         property="beneficiary",
 *         ref="#/components/schemas/Beneficiary",
 *         description="Beneficiario principal del programa"
 *     ),
 *     @OA\Property(
 *         property="program_state",
 *         ref="#/components/schemas/ProgramState",
 *         description="Estado actual del programa"
 *     ),
 *     @OA\Property(
 *         property="country",
 *         ref="#/components/schemas/Country",
 *         description="País donde opera el programa"
 *     ),
 *     @OA\Property(
 *         property="agency",
 *         ref="#/components/schemas/Agency",
 *         description="Agencia ejecutora del programa"
 *     ),
 *     @OA\Property(
 *         property="sdgs",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/SDG"),
 *         description="Objetivos de Desarrollo Sostenible asociados"
 *     ),
 *     @OA\Property(
 *         property="donors",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Donor"),
 *         description="Donantes que financian el programa"
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="Contact",
 *     title="Contact",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Juan Pérez"),
 *     @OA\Property(property="email", type="string", example="juan.perez@example.com"),
 *     @OA\Property(property="phone", type="string", example="+506 8888-8888")
 * )
 * 
 * @OA\Schema(
 *     schema="Beneficiary",
 *     title="Beneficiary",
 *     @OA\Property(property="id", type="integer", example=2),
 *     @OA\Property(property="name", type="string", example="Comunidades Rurales de América Latina")
 * )
 * 
 * @OA\Schema(
 *     schema="ProgramState",
 *     title="ProgramState",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Activo")
 * )
 * 
 * @OA\Schema(
 *     schema="Country",
 *     title="Country",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Costa Rica"),
 *     @OA\Property(
 *         property="currency",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="Colón Costarricense"),
 *         @OA\Property(property="code", type="string", example="CRC"),
 *         @OA\Property(property="symbol", type="string", example="₡")
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="Agency",
 *     title="Agency",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="UNICEF"),
 *     @OA\Property(property="url", type="string", example="https://www.unicef.org"),
 *     @OA\Property(property="is_approved", type="boolean", example=true)
 * )
 * 
 * @OA\Schema(
 *     schema="SDG",
 *     title="SDG",
 *     description="Objetivo de Desarrollo Sostenible (ODS)",
 *     @OA\Property(property="id", type="integer", example=4),
 *     @OA\Property(property="name", type="string", example="Educación de Calidad"),
 *     @OA\Property(property="image", type="string", example="sdg_images/sdg4.png"),
 *     @OA\Property(property="filename", type="string", example="sdg4.png")
 * )
 * 
 * @OA\Schema(
 *     schema="Donor",
 *     title="Donor",
 *     description="Donante financiador",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Banco Mundial")
 * )
 */
class ProgramSchema
{
    // Esta clase solo contiene anotaciones Swagger, no tiene lógica
}
