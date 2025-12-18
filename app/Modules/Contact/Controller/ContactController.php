<?php
namespace App\Modules\Contact\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\ContactResource;
use App\Modules\Contact\Service\ContactService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use RuntimeException;

/**
 * @OA\Schema(
 *     schema="Contact",
 *     type="object",
 *     title="Contact",
 *     description="Persona de contacto responsable de programas o proyectos",
 *     @OA\Property(property="id", type="integer", example=1, description="ID único del contacto"),
 *     @OA\Property(property="first_name", type="string", example="Juan", description="Nombre(s) del contacto"),
 *     @OA\Property(property="last_name", type="string", example="Pérez García", description="Apellido(s) del contacto"),
 *     @OA\Property(property="title", type="string", example="Director de Proyectos", description="Cargo o título del contacto"),
 *     @OA\Property(property="email", type="string", format="email", example="juan.perez@organization.org", description="Email corporativo (único)"),
 *     @OA\Property(property="phone", type="string", example="+591 77123456", description="Número de teléfono")
 * )
 */
class ContactController extends Controller
{
    private ContactService $contactService;

    public function __construct(ContactService $contactService)
    {
        $this->contactService = $contactService;
    }

    /**
     * @OA\Get(
     *     path="/contacts",
     *     tags={"Contacts"},
     *     summary="Listar todos los contactos",
     *     description="Obtiene la lista completa de personas de contacto registradas en el sistema",
     *     @OA\Response(
     *         response=200,
     *         description="Lista obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lista de contactos obtenida exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="contacts",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Contact")
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=15, description="Total de contactos registrados")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error interno del servidor")
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $contacts = $this->contactService->getAllContacts();
            
            return ApiResponse::success(
                'Contact list successfully obtained',
                200,
                [
                    'contacts' => ContactResource::collection($contacts),
                    'total' => $contacts->count()
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/contacts/{id}",
     *     tags={"Contacts"},
     *     summary="Obtener un contacto específico",
     *     description="Obtiene la información detallada de una persona de contacto por su ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del contacto a obtener",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contacto encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Contacto encontrado"),
     *             @OA\Property(property="data", ref="#/components/schemas/Contact")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Contacto no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Contact no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $contact = $this->contactService->getContactById($id);

            return ApiResponse::success(
                'Contact found',
                200,
                new ContactResource($contact)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Contact');
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/contacts",
     *     tags={"Contacts"},
     *     summary="Crear nuevo contacto",
     *     description="Registra una nueva persona de contacto. El email debe ser único en el sistema.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"first_name", "last_name", "title", "email", "phone"},
     *                 @OA\Property(property="first_name", type="string", maxLength=255, example="María", description="Nombre(s) del contacto (requerido)"),
     *                 @OA\Property(property="last_name", type="string", maxLength=255, example="González Rodríguez", description="Apellido(s) del contacto (requerido)"),
     *                 @OA\Property(property="title", type="string", maxLength=255, example="Coordinadora Regional", description="Cargo o título profesional (requerido)"),
     *                 @OA\Property(property="email", type="string", format="email", maxLength=255, example="maria.gonzalez@ngo.org", description="Email corporativo único (requerido)"),
     *                 @OA\Property(property="phone", type="string", maxLength=50, example="+591 2 2345678", description="Número de teléfono (requerido)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Contacto creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Contacto creado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Contact")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación de dominio",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="el nombre debe tener al menos 2 caracteres")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación técnica",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error de validación"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="email",
     *                     type="array",
     *                     @OA\Items(type="string", example="Ya existe un contacto con el email: maria.gonzalez@ngo.org")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function store(ContactRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $contact = $this->contactService->createContact(
                $validated['first_name'],
                $validated['last_name'],
                $validated['title'],
                $validated['email'],
                $validated['phone']
            );

            return ApiResponse::created(
                'Contact created successfully',
                new ContactResource($contact)
            );
        } catch (ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (RuntimeException $e) {
            // Si el error es de duplicado, retornar como error de validación (422)
            if (str_contains($e->getMessage(), 'Ya existe')) {
                return ApiResponse::validationError(['email' => [$e->getMessage()]]);
            }
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/contacts/{id}",
     *     tags={"Contacts"},
     *     summary="Actualizar contacto existente",
     *     description="Actualiza la información de una persona de contacto. El email debe ser único en el sistema.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del contacto a actualizar",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"first_name", "last_name", "title", "email", "phone"},
     *                 @OA\Property(property="first_name", type="string", maxLength=255, example="María Fernanda", description="Nombre(s) actualizado"),
     *                 @OA\Property(property="last_name", type="string", maxLength=255, example="González Rodríguez", description="Apellido(s) actualizado"),
     *                 @OA\Property(property="title", type="string", maxLength=255, example="Directora Regional", description="Cargo actualizado"),
     *                 @OA\Property(property="email", type="string", format="email", maxLength=255, example="mf.gonzalez@ngo.org", description="Email actualizado (debe ser único)"),
     *                 @OA\Property(property="phone", type="string", maxLength=50, example="+591 2 2345679", description="Teléfono actualizado")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contacto actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Contacto actualizado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Contact")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación de dominio"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Contacto no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Contact no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación técnica",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error de validación"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="email",
     *                     type="array",
     *                     @OA\Items(type="string", example="Ya existe un contacto con el email: otro@ngo.org")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function update(ContactRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $contact = $this->contactService->updateContact(
                $id,
                $validated['first_name'],
                $validated['last_name'],
                $validated['title'],
                $validated['email'],
                $validated['phone']
            );

            return ApiResponse::success(
                'Contact uploaded successfully',
                200,
                new ContactResource($contact)
            );
        } catch (ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (RuntimeException $e) {
            if (stripos($e->getMessage(), 'not found') !== false) {
                return ApiResponse::notFound('Contact');
            }
            // Si el error es de duplicado, retornar como error de validación (422)
            if (str_contains($e->getMessage(), 'Ya existe')) {
                return ApiResponse::validationError(['email' => [$e->getMessage()]]);
            }
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/contacts/search",
     *     tags={"Contacts"},
     *     summary="Buscar contacto por email",
     *     description="Busca una persona de contacto específica por su dirección de email (búsqueda exacta, case-insensitive)",
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         required=true,
     *         description="Email del contacto a buscar",
     *         @OA\Schema(type="string", format="email", example="juan.perez@organization.org")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contacto encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Contacto encontrado"),
     *             @OA\Property(property="data", ref="#/components/schemas/Contact")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Contacto no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Contact no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error de validación"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="email",
     *                     type="array",
     *                     @OA\Items(type="string", example="El campo email es obligatorio.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email'
            ]);

            $contact = $this->contactService->findContactByEmail($request->input('email'));

            return ApiResponse::success(
                'Contact found',
                200,
                new ContactResource($contact)
            );
        } catch (ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Contact');
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }
}