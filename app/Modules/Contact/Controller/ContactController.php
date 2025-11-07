<?php
namespace App\Modules\Contact\Controller;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\ContactResource;
use App\Modules\Contact\Service\ContactService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class ContactController extends Controller
{
    private ContactService $contactService;

    public function __construct(ContactService $contactService)
    {
        $this->contactService = $contactService;
    }

    /**
     * Listar todos los contactos
     */
    public function index(): JsonResponse
    {
        try {
            $contacts = $this->contactService->getAllContacts();
            
            return ApiResponse::success(
                'Lista de contactos obtenida exitosamente',
                200,
                [
                    'contacts' => ContactResource::collection($contacts),
                    'total' => $contacts->count()
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * Mostrar un contacto específico
     */
    public function show(int $id): JsonResponse
    {
        try {
            $contact = $this->contactService->getContactById($id);

            return ApiResponse::success(
                'Contacto encontrado',
                200,
                new ContactResource($contact)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Contact');
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * Crear un nuevo contacto
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'title' => 'required|string',
                'email' => 'required|email',
                'phone' => 'required|string',
            ]);

            $contact = $this->contactService->createContact(
                $request->input('first_name'),
                $request->input('last_name'),
                $request->input('title'),
                $request->input('email'),
                $request->input('phone')
            );

            return ApiResponse::created(
                'Contacto creado exitosamente',
                new ContactResource($contact)
            );
        } catch (ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * Actualizar un contacto existente
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'title' => 'required|string',
                'email' => 'required|email',
                'phone' => 'required|string',
            ]);

            $contact = $this->contactService->updateContact(
                $id,
                $request->input('first_name'),
                $request->input('last_name'),
                $request->input('title'),
                $request->input('email'),
                $request->input('phone')
            );

            return ApiResponse::success(
                'Contacto actualizado exitosamente',
                200,
                new ContactResource($contact)
            );
        } catch (ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (RuntimeException $e) {
            if (stripos($e->getMessage(), 'no encontrado') !== false) {
                return ApiResponse::notFound('Contact');
            }
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * Buscar contacto por email
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email'
            ]);

            $contact = $this->contactService->findContactByEmail($request->input('email'));

            return ApiResponse::success(
                'Contacto encontrado',
                200,
                new ContactResource($contact)
            );
        } catch (ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Contact');
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }
}