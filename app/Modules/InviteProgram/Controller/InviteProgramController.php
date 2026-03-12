<?php

namespace App\Modules\InviteProgram\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\InviteProgramRequest;
use App\Http\Resources\InviteProgramResource;
use App\Http\Responses\ApiResponse;
use App\Modules\InviteProgram\Service\InviteProgramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * @OA\Tag(
 *     name="Invite Program",
 *     description="Project-manager invitations to collaborate in a program context"
 * )
 *
 * @OA\Schema(
 *     schema="InviteProgram",
 *     type="object",
 *     title="InviteProgram",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="program_country_user_role_id", type="integer", example=10),
 *     @OA\Property(property="invited_user_role_id", type="integer", example=23),
 *     @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", nullable=true)
 * )
 */
class InviteProgramController extends Controller
{
    private InviteProgramService $service;

    public function __construct(InviteProgramService $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *     path="/invite_programs",
     *     summary="List invites",
     *     description="Get paginated list of invites. Optionally filter by program_country_user_role_id and/or invited_user_role_id.",
     *     operationId="listInvitePrograms",
     *     tags={"Invite Program"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", example=10)),
     *     @OA\Parameter(name="program_country_user_role_id", in="query", required=false, @OA\Schema(type="integer", example=10)),
     *     @OA\Parameter(name="invited_user_role_id", in="query", required=false, @OA\Schema(type="integer", example=23)),
     *     @OA\Response(response=200, description="Invites retrieved successfully"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 10);
        $programCountryUserRoleId = $request->get('program_country_user_role_id')
            ? (int) $request->get('program_country_user_role_id')
            : null;
        $invitedUserRoleId = $request->get('invited_user_role_id')
            ? (int) $request->get('invited_user_role_id')
            : null;

        $invites = $this->service->getAllInvites($perPage, $programCountryUserRoleId, $invitedUserRoleId);

        return ApiResponse::success(
            'Invites retrieved successfully',
            200,
            [
                'invites' => InviteProgramResource::collection($invites),
                'total' => $invites->total(),
                'per_page' => $invites->perPage(),
                'current_page' => $invites->currentPage(),
                'last_page' => $invites->lastPage(),
            ]
        );
    }

    /**
     * @OA\Post(
     *     path="/invite_programs",
     *     summary="Create invite",
     *     description="Create a relation invite between a program owner assignment and an invited project-manager user role.",
     *     operationId="createInviteProgram",
     *     tags={"Invite Program"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"program_country_user_role_id","invited_user_role_id"},
     *             @OA\Property(property="program_country_user_role_id", type="integer", example=10),
     *             @OA\Property(property="invited_user_role_id", type="integer", example=23)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Invite created successfully"),
     *     @OA\Response(response=400, description="Business rule error"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(InviteProgramRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $invite = $this->service->createInvite(
                $validated['program_country_user_role_id'],
                $validated['invited_user_role_id']
            );

            return ApiResponse::created(
                'Invite created successfully',
                new InviteProgramResource($invite)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/invite_programs/{id}",
     *     summary="Get invite by ID",
     *     operationId="getInviteProgram",
     *     tags={"Invite Program"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
     *     @OA\Response(response=200, description="Invite retrieved successfully"),
     *     @OA\Response(response=404, description="Invite not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $invite = $this->service->getInviteById($id);

            return ApiResponse::success(
                'Invite retrieved successfully',
                200,
                new InviteProgramResource($invite)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/invite_programs/{id}",
     *     summary="Delete invite",
     *     description="Remove an invite. Allowed for owner or invited user according to business rules.",
     *     operationId="deleteInviteProgram",
     *     tags={"Invite Program"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
     *     @OA\Response(response=200, description="Invite removed successfully"),
     *     @OA\Response(response=400, description="Business rule error"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Invite not found")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->removeInvite($id);

            return ApiResponse::success('Invite removed successfully', 200);
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }
}
