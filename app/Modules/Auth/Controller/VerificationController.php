<?php

namespace App\Modules\Auth\Controller;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Modules\User\Domain\User;
use App\Notifications\PendingRegistrationForAdminNotification;
use App\Notifications\EmailVerifiedAwaitingApprovalNotification;
use App\Modules\User\Repository\UserRepository;
use App\Modules\UserState\Repository\UserStateRepository;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

/**
 * @OA\Tag(
 *     name="Email Verification",
 *     description="Email verification endpoints"
 * )
 */
class VerificationController extends Controller
{
    private UserRepository $userRepository;
    private UserStateRepository $userStateRepository;

    public function __construct(
        UserRepository $userRepository,
        UserStateRepository $userStateRepository
    ) {
        $this->userRepository = $userRepository;
        $this->userStateRepository = $userStateRepository;
    }

    /**
     * Verify user email
     * 
     * @OA\Get(
     *     path="/api/v1/email/verify/{id}/{hash}",
     *     tags={"Email Verification"},
     *     summary="Verify user email address",
     *     description="Verifies user email using signed URL. Changes user state from 'unverified' to 'pending'.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="hash",
     *         in="path",
     *         required=true,
     *         description="Verification hash",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email verified successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Email verified successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="message", type="string"),
     *                 @OA\Property(property="user", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid verification link or email already verified"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function verify(Request $request, int $id, string $hash): JsonResponse
    {
        try {
            // Find user
            $user = $this->userRepository->findById($id);

            // Verify hash matches user's email
            if (!hash_equals($hash, sha1($user->email))) {
                return ApiResponse::error('Invalid verification link', 400);
            }

            // Check if already verified
            if ($user->hasVerifiedEmail()) {
                return ApiResponse::success('Email already verified', 200, [
                    'message' => 'Your email has already been verified. An administrator will review your request soon.'
                ]);
            }

            // Mark email as verified
            $user->markEmailAsVerified();

            // Change user state from 'unverified' to 'pending'
            $pendingState = $this->userStateRepository->findBy('name', 'pending');
            $user->user_state_id = $pendingState->id;
            $this->userRepository->save($user);

            // Notify user that email is verified and awaiting admin approval
            $user->notify(new EmailVerifiedAwaitingApprovalNotification());

            $requestedRole = $user->roles()->pluck('name')->implode(', ');
            $pendingNotification = new PendingRegistrationForAdminNotification(
                $user->name,
                $user->email,
                $requestedRole !== '' ? $requestedRole : 'N/A'
            );

            // Notify active admin users that a new registration requires review.
            $admins = User::whereHas('roles', function ($query) {
                $query->where('name', 'admin');
            })->whereHas('userState', function ($query) {
                $query->where('name', 'active');
            })->get();

            if ($admins->isNotEmpty()) {
                Notification::send($admins, $pendingNotification);
            }

            // Also notify configured admin inboxes (useful when admin mailbox is not a user account).
            $adminUserEmails = $admins->pluck('email')
                ->filter()
                ->map(fn ($email) => strtolower(trim((string) $email)))
                ->unique()
                ->values()
                ->all();

            $configuredAdminEmails = collect(config('mail.admin_notification_emails', []))
                ->filter()
                ->map(fn ($email) => strtolower(trim((string) $email)))
                ->unique()
                ->reject(fn ($email) => in_array($email, $adminUserEmails, true));

            foreach ($configuredAdminEmails as $adminEmail) {
                Notification::route('mail', $adminEmail)->notify($pendingNotification);
            }

            // Fire Laravel Verified event (for observers/listeners)
            event(new Verified($user));

            return ApiResponse::success('Email verified successfully', 200, [
                'message' => 'Your email has been verified successfully. An administrator will review your access request soon.',
                'user' => new \App\Http\Resources\UserResource($user->load('roles', 'userState'))
            ]);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
    }

    /**
     * Resend verification email
     * 
     * @OA\Post(
     *     path="/api/v1/email/resend",
     *     tags={"Email Verification"},
     *     summary="Resend verification email",
     *     description="Resends verification email to user's email address",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Verification email resent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Verification email resent successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="message", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Email already verified"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function resend(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        try {
            $user = $this->userRepository->findBy('email', $request->email);

            if ($user->hasVerifiedEmail()) {
                return ApiResponse::error('Email already verified', 400);
            }

            // Resend verification email
            $user->sendEmailVerificationNotification();

            return ApiResponse::success('Verification email resent successfully', 200, [
                'message' => 'A verification email has been resent. Please check your inbox.'
            ]);
        } catch (\Exception $e) {
            return ApiResponse::error('User not found', 404);
        }
    }
}
