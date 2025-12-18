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

class ContactController extends Controller
{
    private ContactService $contactService;

    public function __construct(ContactService $contactService)
    {
        $this->contactService = $contactService;
    }

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
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

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
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

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